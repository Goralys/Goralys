#![cfg_attr(windows, feature(abi_vectorcall))]
mod font;
mod input;
mod slots;
pub mod types;
mod utils;

use crate::font::{Font, load_marianne, register_font};
use crate::input::parse_input;
use crate::slots::{Kind, add_slot_ops, compute_slots_pos, parse_p_slots, parse_slots};
use crate::types::GResult;
use base64::{Engine as _, engine::general_purpose};
use chrono::Datelike;
use ext_php_rs::prelude::*;
use ironpress::{HtmlConverter, PageSize};
use lopdf::Document;
use lopdf::content::{Content, Operation};
use rayon::prelude::*;
use std::fs;
use std::path::Path;
use std::sync::Arc;
use ttf_parser::Face;

fn load_image_as_data_uri(path: &str) -> GResult<String> {
    let data = fs::read(path)?;
    let b64 = general_purpose::STANDARD.encode(&data);
    Ok(format!("data:image/jpeg;base64,{}", b64))
}

fn load_font_as_data_uri(path: &str, mime_type: &str) -> GResult<String> {
    let data = fs::read(path)?;
    let b64 = general_purpose::STANDARD.encode(&data);
    Ok(format!("data:{};base64,{}", mime_type, b64))
}

fn inject_fonts_in_css(
    template_dir: &String,
    base_font: &String,
    mut css: String,
) -> GResult<String> {
    let dir = template_dir.to_owned();
    // Replace URLs in CSS
    for suffix in ["Regular", "Bold", "Italic", "BoldItalic"] {
        let file = format!("fonts/{base_font}-{suffix}.ttf");
        let uri = load_font_as_data_uri(format!("{dir}{file}").as_str(), "font/truetype")
            .map_err(|e| format!("{dir}{file}: {e}"))?;
        css = css.replace(&format!("url('{file}')"), &format!("url('{uri}')"));
    }

    Ok(css)
}

pub fn build_metadata(path: String) -> GResult<()> {
    let input_file: &str = path.as_str();
    let batch = Arc::new(parse_input(input_file)?);
    let template_dir = batch.template_dir.clone();
    let base_font = batch.base_font.clone();
    let pdfium_path = format!("{template_dir}{}", batch.pdfium.clone());

    // Read CSS
    let css = fs::read_to_string(template_dir.clone() + "style.css").map_err(|e| {
        format!(
            "CSS not found: {}, searched at {}",
            e,
            template_dir.clone() + "style.css"
        )
    })?;

    // Read HTML template
    let mut template = fs::read_to_string(template_dir.clone() + "main.html")
        .map_err(|e| format!("HTML template not found: {}", e))?;

    // Convert image to data URI
    let logo_file = format!("{template_dir}assets/{}", batch.logo_file.clone());
    match load_image_as_data_uri(logo_file.as_str()) {
        Ok(logo_uri) => {
            template = template.replace(
                &format!(r#"src="./assets/{}""#, batch.logo_file.clone()),
                &format!(r#"src="{}""#, logo_uri),
            );
        }
        Err(e) => {
            eprintln!(
                "Image not found at {} ({}), continuing without it",
                logo_file, e
            );
        }
    }

    // Inject fonts into CSS
    let css_with_fonts = match inject_fonts_in_css(&template_dir, &base_font, css) {
        Ok(css) => css,
        Err(e) => {
            eprintln!("Cannot load fonts ({}), using system fonts as fallback", e);
            fs::read_to_string(template_dir.clone() + "style-default.css")?
        }
    };

    template = template.replacen(
        "</head>",
        &format!("<style>\n{css_with_fonts}\n</style>\n</head>"),
        1,
    );

    // resolve current year
    template = template.replace("{year}", chrono::Local::now().year().to_string().as_str());

    let font_dir = template_dir.clone() + "fonts/";
    let converter = HtmlConverter::new()
        .add_font(
            base_font.as_str(),
            fs::read(format!("{font_dir}{base_font}-Regular.ttf"))?,
        )
        .add_font(
            format!("{base_font}-Bold").as_str(),
            fs::read(format!("{font_dir}{base_font}-Bold.ttf"))?,
        )
        .add_font(
            format!("{base_font}-Italic").as_str(),
            fs::read(format!("{font_dir}{base_font}-Italic.ttf"))?,
        )
        .add_font(
            format!("{base_font}-BoldItalic").as_str(),
            fs::read(format!("{font_dir}{base_font}-BoldItalic.ttf"))?,
        )
        .page_size(PageSize::A4);

    let file = fs::File::create(format!("{template_dir}p_slots.json"))?;
    let slots = compute_slots_pos(&pdfium_path, &template, &converter, parse_slots(&template))?;
    serde_json::to_writer_pretty(file, &slots)?;

    let sentinel = converter.convert(&template)?;
    for s in slots {
        let needle = match &s.s.kind {
            Kind::Format => s.s.text,
            _ => format!("{{{}}}", s.s.name),
        };
        template = template.replace(needle.as_str(), "");
    }
    let empty: Vec<u8> = converter.convert(&template)?;

    fs::write(format!("{template_dir}sentinel.pdf"), sentinel)?; // optional but useful for debugging
    fs::write(format!("{template_dir}empty.pdf"), empty)?;

    Ok(())
}

pub fn render_overlay(path: String) -> GResult<()> {
    let input_file: &str = path.as_str();
    let batch = parse_input(input_file)?;
    let template_dir = batch.template_dir.clone();
    let base_font = batch.base_font.clone();

    let slots_path = format!("{template_dir}p_slots.json");
    let p_slots = parse_p_slots(slots_path.as_str())?;

    let mut doc = Document::load(format!("{template_dir}empty.pdf"))?;
    let pages = doc.get_pages();
    let page_id = pages.get(&1).expect("Invalid pdf, no first page");

    let raw = fs::read(format!("{template_dir}fonts/{base_font}-Regular.ttf"))?;
    let font = Face::parse(&raw, 0)?;
    let marianne = load_marianne(&mut doc, &raw, &font, &base_font, Font::Regular)?;

    let raw = fs::read(format!("{template_dir}fonts/{base_font}-Bold.ttf"))?;
    let font_bold = Face::parse(&raw, 0)?;
    let marianne_bold = load_marianne(&mut doc, &raw, &font_bold, &base_font, Font::Bold)?;
    register_font(
        &mut doc,
        *page_id,
        format!("{base_font}").as_str(),
        marianne,
    )?;
    register_font(
        &mut doc,
        *page_id,
        format!("{base_font}-Bold").as_str(),
        marianne_bold,
    )?;

    let doc = Arc::new(doc);
    let batch = Arc::new(batch);

    let pool = rayon::ThreadPoolBuilder::new()
        .num_threads(batch.max_threads)
        .build()
        .map_err(|e| e.to_string())?;

    let base_name = base_font.as_str();
    let bold_name = format!("{base_font}-Bold");
    let bold_name = bold_name.as_str();
    pool.install(|| {
        batch
            .jobs
            .par_iter()
            .map(|j| -> Result<(), String> {
                let mut doc = (*doc).clone();
                let mut vars = j.vars.clone();
                vars.insert(
                    "nom-complet".to_string(),
                    format!("{} {}", vars["nom"], vars["prénom"]),
                );

                let mut ops = vec![Operation::new("q", vec![])];
                for slot in &p_slots {
                    let (font_name, face) = match slot.s.font {
                        Font::Bold => (bold_name, &font_bold),
                        _ => (base_name, &font),
                    };
                    add_slot_ops(&mut ops, slot, face, font_name, &vars, &j.flags)
                        .map_err(|e| e.to_string())?
                }
                ops.push(Operation::new("Q", vec![]));

                let content = Content { operations: ops }
                    .encode()
                    .map_err(|e| e.to_string())?;
                doc.add_page_contents(*page_id, content)
                    .map_err(|e| e.to_string())?;

                if let Some(dir) = Path::new(&j.output_file).parent() {
                    fs::create_dir_all(dir).map_err(|e| e.to_string())?;
                }
                doc.save(&j.output_file).map_err(|e| e.to_string())?;
                Ok(())
            })
            .collect::<Result<(), String>>()
    })?;

    Ok(())
}

/// Exports all jobs from a given JSON file.
///
/// @param string $path The file containing the jobs to export.
///
/// @return void
#[php_function]
pub fn export_jobs_from(path: String) -> PhpResult<()> {
    render_overlay(path).map_err(|e| e.to_string().into())
}

/// Gives you a nice greeting.
///
/// @param string $name Your name.
///
/// @return string A kind message for you.
#[php_function]
pub fn hello(name: String) -> String {
    format!("Hello, {} !", name)
}

#[php_module]
pub fn module(module: ModuleBuilder) -> ModuleBuilder {
    module
        .function(wrap_function!(export_jobs_from))
        .function(wrap_function!(hello))
}
