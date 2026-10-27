#![cfg_attr(windows, feature(abi_vectorcall))]
mod input;

use crate::input::{Job, parse_input};
use base64::{Engine as _, engine::general_purpose};
use ext_php_rs::prelude::*;
use ironpress::{HtmlConverter, Margin, PageSize};
use rayon::prelude::*;
use regex::{Captures, Regex};
use std::fs;
use std::path::Path;
use std::sync::Arc;

fn load_image_as_data_uri(path: &str) -> Result<String, Box<dyn std::error::Error>> {
    let data = fs::read(path)?;
    let b64 = general_purpose::STANDARD.encode(&data);
    Ok(format!("data:image/jpeg;base64,{}", b64))
}

fn load_font_as_data_uri(
    path: &str,
    mime_type: &str,
) -> Result<String, Box<dyn std::error::Error>> {
    let data = fs::read(path)?;
    let b64 = general_purpose::STANDARD.encode(&data);
    Ok(format!("data:{};base64,{}", mime_type, b64))
}

fn inject_fonts_in_css(
    template_dir: &String,
    mut css: String,
) -> Result<String, Box<dyn std::error::Error>> {
    let dir = template_dir.to_owned();

    // Load the 4 weights for Marianne
    let regular = load_font_as_data_uri(
        &*(dir.clone() + "fonts/Marianne-Regular.ttf"),
        "font/truetype",
    )?;
    let bold = load_font_as_data_uri(&*(dir.clone() + "fonts/Marianne-Bold.ttf"), "font/truetype")?;
    let italic = load_font_as_data_uri(
        &*(dir.clone() + "fonts/Marianne-Italic.ttf"),
        "font/truetype",
    )?;
    let bold_italic = load_font_as_data_uri(
        &*(dir.clone() + "fonts/Marianne-BoldItalic.ttf"),
        "font/truetype",
    )?;

    // Replace URLs in CSS
    css = css.replace(
        "url('fonts/Marianne-Regular.ttf')",
        &format!("url('{}')", regular),
    );
    css = css.replace(
        "url('fonts/Marianne-Bold.ttf')",
        &format!("url('{}')", bold),
    );
    css = css.replace(
        "url('fonts/Marianne-Italic.ttf')",
        &format!("url('{}')", italic),
    );
    css = css.replace(
        "url('fonts/Marianne-BoldItalic.ttf')",
        &format!("url('{}')", bold_italic),
    );

    Ok(css)
}

fn replace_placeholders(template: &str, j: &Job) -> String {
    let mut result = template.to_string();
    for (var, val) in &j.vars {
        let placeholder = format!("{{{{{var}}}}}"); // {{name}}
        result = result.replace(&placeholder, &val);
    }

    let re = Regex::new(
        r#"\{\{\s*(not\s+|!)?(\w+)\s*\?\s*["']([^"']*)["']?\s*:\s*["']([^"']*)["']?\s*}}"#,
    )
    .unwrap();

    result = re
        .replace_all(&result, |c: &Captures| {
            let negate = c.get(1).is_some();
            let value = j.flags.get(&c[2]).copied().unwrap_or(false);

            if value != negate {
                c[3].to_string()
            } else {
                c[4].to_string()
            }
        })
        .into_owned();

    result
}

pub fn export_jobs(path: String) -> Result<(), Box<dyn std::error::Error>> {
    let input_file: &str = path.as_str();
    let batch = Arc::new(parse_input(input_file)?);
    let template_dir = batch.template_dir.clone();

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
    match load_image_as_data_uri(&*(template_dir.clone() + "assets/logo-nantes.jpg")) {
        Ok(logo_uri) => {
            template = template.replace(
                r#"<img src="./assets/logo-nantes.jpg""#,
                &format!(r#"<img src="{}""#, logo_uri),
            );
        }
        Err(e) => {
            eprintln!("Image not found ({}), continuing without it", e);
        }
    }

    // Inject fonts into CSS
    let css_with_fonts = match inject_fonts_in_css(&template_dir, css) {
        Ok(css) => css,
        Err(e) => {
            eprintln!("Cannot load fonts ({}), using system fonts as fallback", e);
            fs::read_to_string(template_dir.clone() + "style-marianne.css")?
        }
    };

    template = template.replacen(
        "</head>",
        &format!("<style>\n{css_with_fonts}\n</style>\n</head>"),
        1,
    );

    let template = Arc::new(template);
    let converter = HtmlConverter::new()
        .page_size(PageSize::A4)
        .margin(Margin {
            top: 5.0,
            right: 5.0,
            bottom: 5.0,
            left: 5.0,
        })
        .sanitize(false);
    let converter = Arc::new(converter);

    let results: Vec<Result<(), String>> = batch
        .jobs
        .par_iter()
        .map(|job| {
            let output = job.output_file.clone();
            let resolved = replace_placeholders(&template, job);
            let pdf = Arc::clone(&converter)
                .convert(&resolved)
                .map_err(|e| format!("Ironpress error: {}", e))?;

            if let Some(dir) = Path::new(&output).parent() {
                fs::create_dir_all(dir).map_err(|e| e.to_string())?;
            }
            fs::write(output, pdf).map_err(|e| e.to_string())?;
            Ok(())
        })
        .collect();

    for result in results {
        if let Err(e) = result {
            eprintln!("Job failed: {}", e);
        }
    }

    Ok(())
}

/// Exports all jobs from a given JSON file.
///
/// @param string $path The file containing the jobs to export.
///
/// @return void
#[php_function]
pub fn export_jobs_from(path: String) -> PhpResult<()> {
    export_jobs(path).map_err(|e| e.to_string().into())
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
