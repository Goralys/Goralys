use crate::font::{Font, measure_text};
use crate::types::GResult;
use crate::utils::load_pdfium;
use encoding_rs::WINDOWS_1252;
use ironpress::HtmlConverter;
use lopdf::Object;
use lopdf::content::Operation;
use pdfium_render::prelude::PdfSearchDirection::SearchForward;
use pdfium_render::prelude::*;
use scraper::ElementRef;
use serde::{Deserialize, Serialize};
use serde_json::from_str;
use std::collections::HashMap;
use ttf_parser::Face;

#[derive(Clone, Eq, PartialEq, Serialize, Deserialize)]
pub enum Align {
    Right,
    Center,
    Left,

    Unknown,
}

impl Align {
    fn from(s: String) -> Align {
        if s == "right" {
            Align::Right
        } else if s == "center" {
            Align::Center
        } else if s == "left" {
            Align::Left
        } else {
            Align::Unknown
        }
    }
}

#[derive(Clone, Eq, PartialEq, Serialize, Deserialize)]
pub enum VAlign {
    Top,
    Middle,
    Bottom,

    Unknown,
}

impl VAlign {
    fn from(s: String) -> VAlign {
        if s == "top" {
            VAlign::Top
        } else if s == "middle" {
            VAlign::Middle
        } else if s == "bottom" {
            VAlign::Bottom
        } else {
            VAlign::Unknown
        }
    }
}

#[derive(Clone, Eq, PartialEq, Serialize, Deserialize)]
pub enum Kind {
    Text,
    CheckBox,
    Format,

    Unknown,
}

impl Kind {
    fn from(s: String) -> Kind {
        if vec!["check", "checkbox", "cbx", "box"].contains(&s.as_str()) {
            Kind::CheckBox
        } else if s == "text" {
            Kind::Text
        } else if s == "format" {
            Kind::Format
        } else {
            Kind::Unknown
        }
    }
}

#[derive(Clone, Serialize, Deserialize)]
pub struct Slot {
    pub name: String,
    pub w: f32,
    pub h: f32,
    pub font_size: f32,
    pub font: Font,
    pub align: Align,
    pub v_align: VAlign,
    pub kind: Kind,
    pub text: String,
}

#[derive(Copy, Clone, Serialize, Deserialize)]
pub struct Pos {
    pub x: f32,
    pub y: f32,
}

#[derive(Copy, Clone, Serialize, Deserialize)]
pub struct SlotCoord {
    pub p: Pos,
    pub lh: f32,
}

#[derive(Serialize, Deserialize)]
pub struct PSlot {
    pub s: Slot,
    pub c: Vec<SlotCoord>,
}

pub fn parse_p_slots(path: &str) -> GResult<Vec<PSlot>> {
    let raw = std::fs::read_to_string(path)?;
    let b: Vec<PSlot> = from_str(raw.as_str())?;
    Ok(b)
}

pub fn parse_slot(el: &ElementRef) -> Slot {
    let v = el.value();

    let name = v
        .attr("data-slot")
        .expect("Slot must have data-slot")
        .to_string();
    let req = |a: &str| {
        v.attr(a)
            .unwrap_or_else(|| panic!("slot '{name}': missing {a}"))
    };
    let num = |a: &str| -> f32 {
        req(a)
            .parse()
            .unwrap_or_else(|_| panic!("slot '{name}': {a} is not a number"))
    };

    let kind = v.attr("data-kind").unwrap_or("text").to_string();
    let text = v.attr("data-text").unwrap_or("").to_string();
    let w = num("data-w");
    let h = v
        .attr("data-h")
        .unwrap_or("-1")
        .trim()
        .parse()
        .expect("data-h must be a number");
    let size = if kind == "check" {
        0.0
    } else {
        num("data-size")
    };
    let font = v.attr("data-font").unwrap_or("regular").to_string();
    let align = v.attr("data-align").unwrap_or("left").to_string();
    let valign = v.attr("data-valign").unwrap_or("top").to_string();

    Slot {
        name,
        w,
        h,
        font_size: size,
        font: Font::from(font),
        align: Align::from(align),
        v_align: VAlign::from(valign),
        kind: Kind::from(kind),
        text,
    }
}

pub fn parse_slots(html: &String) -> Vec<Slot> {
    let doc = scraper::Html::parse_document(html.as_str());
    let selector = scraper::Selector::parse("span.slot").unwrap();

    doc.select(&selector).map(|el| parse_slot(&el)).collect()
}

fn with_page<R>(
    template: &String,
    pdfium: &Pdfium,
    converter: &HtmlConverter,
    f: impl FnOnce(&PdfPage) -> GResult<R>,
) -> GResult<R> {
    let result = converter.convert(template)?;

    let doc = pdfium.load_pdf_from_byte_slice(&result, None)?;
    let page = doc.pages().get(0)?;
    f(&page)
}

fn find_checkboxes(page: &PdfPage, slots: &Vec<Slot>) -> GResult<Vec<PSlot>> {
    let mut squares: Vec<(f32, f32, f32, f32)> = Vec::new(); // left, bottom, right, top

    let checkboxes: Vec<&Slot> = slots.iter().filter(|a| a.kind == Kind::CheckBox).collect();

    for object in page.objects().iter() {
        if let Some(path) = object.as_path_object() {
            let b = path.bounds()?;
            let r = (
                b.left().value,
                b.bottom().value,
                b.right().value,
                b.top().value,
            );
            let w = (b.right().value - b.left().value).abs();
            let h = (b.top().value - b.bottom().value).abs();

            // check if the path is an empty square of about the size of a checkbox
            if (w - h).abs() < 0.5 && (3.0..=6.0).contains(&w) {
                squares.push(r);
            }
        }
    }

    let center = |r: &(f32, f32, f32, f32)| ((r.0 + r.2) / 2.0, (r.1 + r.3) / 2.0);
    let same = |a: (f32, f32), b: (f32, f32)| (a.0 - b.0).abs() < 0.5 && (a.1 - b.1).abs() < 0.5;

    let mut boxes: Vec<_> = squares
        .clone()
        .into_iter()
        .filter(|a| {
            squares
                .iter()
                .filter(|b| same(center(a), center(b)))
                .count()
                == 1
        })
        .collect();

    if boxes.len() != checkboxes.len() {
        return Err(format!(
            "found {} boxes for {} checkbox slots: {:?}",
            boxes.len(),
            checkboxes.len(),
            boxes
        )
        .into());
    }

    boxes.sort_by(|a, b| b.1.total_cmp(&a.1).then(a.0.total_cmp(&b.0)));

    let mut result: Vec<PSlot> = Vec::new();
    for (b, cb) in boxes.iter().zip(checkboxes) {
        let pos = center(b);
        result.push(PSlot {
            s: cb.clone(),
            c: vec![SlotCoord {
                p: Pos { x: pos.0, y: pos.1 },
                lh: 0.0,
            }],
        })
    }

    Ok(result)
}

fn compute_positions(page: &PdfPage, slots: &Vec<Slot>) -> GResult<Vec<PSlot>> {
    let text = page.text()?;

    let mut p_slots: Vec<PSlot> = Vec::new();
    let mut seen: HashMap<String, bool> = HashMap::new();
    for s in slots.into_iter() {
        let s = s.clone();
        let name = s.name.clone();
        if *seen.get(&name).unwrap_or(&false) {
            continue;
        }

        let needle = match s.kind {
            Kind::Format => s.text.clone(),
            _ => format!("{{{}}}", s.name),
        };

        let search = text.search(needle.as_str(), &PdfSearchOptions::new())?;
        let mut pos: Vec<SlotCoord> = Vec::new();
        for segment in search.iter(SearchForward) {
            let o = segment.get(0)?.chars()?.first()?.origin()?;
            pos.push(SlotCoord {
                p: Pos {
                    x: o.0.value,
                    y: o.1.value,
                },
                lh: 0.0,
            });
        }
        pos.sort_by(|a, b| b.p.y.total_cmp(&a.p.y).then(a.p.x.total_cmp(&b.p.x)));

        seen.insert(name, true);
        p_slots.push(PSlot { s, c: pos });
    }

    Ok(p_slots)
}

pub fn compute_slots_pos(
    pdfium_path: &String,
    template: &String,
    converter: &HtmlConverter,
    slots: Vec<Slot>,
) -> GResult<Vec<PSlot>> {
    let pdfium = load_pdfium(pdfium_path)?;

    // gather checkboxes first
    let mut p_slots = with_page(&template, &pdfium, converter, |p| {
        find_checkboxes(p, &slots)
    })?;

    let real_pos = with_page(&template, &pdfium, converter, |p| {
        compute_positions(p, &slots)
    })?;
    let mut template = template.clone();
    for s in slots.iter().clone() {
        let needle = match s.kind {
            Kind::Format => s.text.clone(),
            _ => format!("{{{}}}", s.name),
        };
        template = template.replace(needle.as_str(), format!("{0}<br>{0}", needle).as_str())
    }

    let dummy_pos = with_page(&template, &pdfium, converter, |p| {
        compute_positions(p, &slots)
    })?;

    for (real, dummy) in real_pos.iter().zip(&dummy_pos) {
        let mut coord: Vec<SlotCoord> = Vec::new();
        for (k, r) in real.c.iter().enumerate() {
            // we now have two occurrences of every sentinel
            let lh = dummy.c[2 * k].p.y - dummy.c[2 * k + 1].p.y;
            coord.push(SlotCoord { p: r.p, lh })
        }
        p_slots.push(PSlot {
            s: real.s.clone(),
            c: coord,
        })
    }

    Ok(p_slots)
}

fn wrap_to_slot(slot: &PSlot, content: &String, font: &Face) -> Vec<String> {
    let mut lines: Vec<String> = Vec::new();
    let mut line = String::new();

    for word in content.split_whitespace() {
        let candidate = {
            if line.is_empty() {
                word.to_string()
            } else {
                format!("{line} {word}")
            }
        };
        if line.is_empty() || measure_text(&candidate, font, slot.s.font_size) <= slot.s.w {
            line = candidate;
        } else {
            lines.push(std::mem::take(&mut line));
            line = word.to_string();
        }
    }

    if !line.is_empty() {
        lines.push(line);
    }
    lines
}

fn draw_checkbox(
    ops: &mut Vec<Operation>,
    slot: &PSlot,
    flags: &HashMap<String, bool>,
) -> GResult<()> {
    let w = slot.s.w;

    let (flag, is_yes, failed) = {
        if let Some(base) = slot.s.name.strip_suffix("_yes") {
            (base, true, false)
        } else if let Some(base) = slot.s.name.strip_suffix("_no") {
            (base, false, false)
        } else {
            ("dummy", true, true)
        }
    };

    if failed {
        return Err(format!(
            "Invalid slot name {}, checkbox name must end with _yes or _no.",
            slot.s.name
        )
        .into());
    }

    let checked = flags.get(flag).copied().unwrap_or(false) == is_yes;
    if checked {
        for c in &slot.c {
            ops.push(Operation::new("rg", vec![0.into(), 0.into(), 0.into()])); // black fill
            ops.push(Operation::new(
                "re",
                vec![
                    (c.p.x - w / 2.0).into(), // pos = center, we move to the origin
                    (c.p.y - w / 2.0).into(),
                    w.into(),
                    w.into(),
                ],
            ));
            // apply the fill
            ops.push(Operation::new("f", vec![]));
        }
    }

    Ok(())
}

pub fn add_slot_ops(
    ops: &mut Vec<Operation>,
    slot: &PSlot,
    font: &Face,
    font_name: &str,
    vars: &HashMap<String, String>,
    flags: &HashMap<String, bool>,
) -> GResult<()> {
    if slot.s.kind == Kind::CheckBox {
        return draw_checkbox(ops, slot, flags);
    }

    let content = match slot.s.kind {
        Kind::Format => {
            let mut text = slot.s.text.clone();
            for (k, v) in vars {
                text = text.replace(format!("[{}]", k).as_str(), v.as_str());
            }
            text
        }
        Kind::Text => vars
            .get(&slot.s.name)
            .expect(format!("Invalid var name: {}", &slot.s.name).as_str())
            .clone(),
        _ => "".into(),
    };
    let sentinel = match slot.s.kind {
        Kind::Format => slot.s.text.clone(),
        _ => format!("{{{}}}", slot.s.name.clone()),
    };

    let sw = measure_text(sentinel.as_str(), font, slot.s.font_size);

    for coord in &slot.c {
        let lines = wrap_to_slot(slot, &content, font);
        for (i, line) in lines.iter().enumerate() {
            let mut y_offset = i as f32 * -coord.lh;
            y_offset += match slot.s.v_align {
                VAlign::Middle => (lines.len() - 1) as f32 * coord.lh / 2.0,
                _ => 0.0,
            };
            let lw = measure_text(line.as_str(), font, slot.s.font_size);
            let x_offset = match slot.s.align {
                Align::Center => (sw - lw) / 2.0,

                Align::Right => sw - lw,

                _ => 0.0,
            };

            ops.push(Operation::new("BT", vec![]));
            ops.push(Operation::new(
                "Tf",
                vec![
                    Object::Name(font_name.as_bytes().to_vec()),
                    slot.s.font_size.into(),
                ],
            ));
            ops.push(Operation::new(
                "Td",
                vec![(coord.p.x + x_offset).into(), (coord.p.y + y_offset).into()],
            ));
            ops.push(Operation::new(
                "Tj",
                vec![Object::string_literal(WINDOWS_1252.encode(line.as_str()).0)],
            ));
            ops.push(Operation::new("ET", vec![]));
        }
    }

    Ok(())
}
