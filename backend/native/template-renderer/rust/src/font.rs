use crate::types::GResult;
use lopdf::{Dictionary, Document, Object, ObjectId, dictionary};
use serde::{Deserialize, Serialize};
use ttf_parser::Face;

#[derive(Clone, Eq, PartialEq, Serialize, Deserialize)]
pub enum Font {
    Regular,
    Bold,
    Italic,

    Unknown,
}

impl Font {
    pub fn from(s: String) -> Font {
        if s == "regular" {
            Font::Regular
        } else if s == "bold" {
            Font::Bold
        } else if s == "italic" {
            Font::Italic
        } else {
            Font::Unknown
        }
    }

    pub fn get_name(base_font: &str, variant: &Font) -> String {
        match variant {
            Font::Bold => format!("{base_font}-Bold"),
            Font::Italic => format!("{base_font}-Italic"),
            _ => base_font.to_string(),
        }
    }
}

pub fn load_marianne(
    doc: &mut Document,
    raw: &Vec<u8>,
    font: &Face,
    base_font: &String,
    variant: Font,
) -> GResult<ObjectId> {
    let b = font.global_bounding_box();

    let mut stream = lopdf::Stream::new(
        dictionary! {"Length1" => Object::from(raw.len() as i64)},
        raw.clone(),
    );
    stream.compress()?;
    let stream_id = doc.add_object(stream);
    let name = Font::get_name(base_font, &variant);
    let name = name.as_str();

    let u_per_em = font.units_per_em() as f32;
    let widths: Vec<Object> = (32u8..=255)
        .map(|code| {
            let bytes = [code];
            let (s, _) = encoding_rs::WINDOWS_1252.decode_without_bom_handling(&bytes);
            let w = s
                .chars()
                .next()
                .and_then(|c| font.glyph_index(c))
                .and_then(|g| font.glyph_hor_advance(g))
                .map(|a| (a as f32 * 1000.0 / u_per_em).round() as i64)
                .unwrap_or(0);
            Object::Integer(w)
        })
        .collect();

    let font_descriptor = doc.add_object(dictionary!{
        "Type" => "FontDescriptor",
        "FontName" => name,
        "Flags" => 32,
        "FontBBox" => Object::Array(vec!(b.x_min.into(), b.y_min.into(), b.x_max.into(), b.y_max.into())),
        "Ascent" => font.ascender(),
        "Descent" => font.descender(),
        "CapHeight" => font.capital_height().expect("Invalid capital height"),
        "ItalicAngle" => match variant {
            Font::Italic => -12,
            _ => 0
        },
        "StemV" => match variant {
            Font::Bold => 120,
            _ => 80
        },
        "FontFile2" => Object::Reference(stream_id),
    });

    let font_dictionary = doc.add_object(dictionary! {
        "Type" => "Font",
        "Subtype" => "TrueType",
        "BaseFont" => name,
        "Encoding" => "WinAnsiEncoding",
        "FirstChar" => 32,
        "LastChar" => 255,
        "Widths" => Object::Array(widths),
        "FontDescriptor" => Object::Reference(font_descriptor),
    });

    Ok(font_dictionary)
}

pub fn register_font(
    doc: &mut Document,
    page_id: ObjectId,
    name: &str,
    font_id: ObjectId,
) -> Result<(), lopdf::Error> {
    let font_ref: Option<ObjectId> = {
        let res = doc.get_or_create_resources(page_id)?.as_dict_mut()?;
        if !res.has(b"Font") {
            res.set(b"Font", Dictionary::new());
        }

        match res.get(b"Font")? {
            Object::Reference(id) => Some(*id),
            _ => None,
        }
    };

    let fonts = match font_ref {
        Some(id) => doc.get_object_mut(id)?.as_dict_mut()?,
        None => doc
            .get_or_create_resources(page_id)?
            .as_dict_mut()?
            .get_mut(b"Font")?
            .as_dict_mut()?,
    };
    fonts.set(name, Object::Reference(font_id));

    Ok(())
}

pub fn measure_text(t: &str, font: &Face, size: f32) -> f32 {
    let upem = font.units_per_em() as f32;
    t.chars()
        .filter(|c| !c.is_control())
        .map(|c| {
            font.glyph_index(c)
                .and_then(|g| font.glyph_hor_advance(g))
                .unwrap_or(0) as f32
        })
        .sum::<f32>()
        * size
        / upem
}
