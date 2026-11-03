use crate::types::GResult;
use serde::Deserialize;
use serde_json::from_str;
use std::collections::HashMap;

#[derive(Deserialize, Clone)]
pub struct Job {
    pub output_file: String,
    pub vars: HashMap<String, String>,
    pub flags: HashMap<String, bool>,
}

#[derive(Deserialize)]
pub struct ExportBatch {
    pub max_threads: usize,
    pub template_dir: String,
    pub logo_file: String,
    pub base_font: String,
    pub pdfium: String,
    pub jobs: Vec<Job>,
}

pub fn parse_input(path: &str) -> GResult<ExportBatch> {
    let raw = std::fs::read_to_string(path)?;
    let b: ExportBatch = from_str(raw.as_str())?;
    Ok(b)
}
