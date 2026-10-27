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
    pub template_dir: String,
    pub jobs: Vec<Job>,
}

pub fn parse_input(path: &str) -> Result<ExportBatch, Box<dyn std::error::Error>> {
    let raw = std::fs::read_to_string(path)?;
    let b: ExportBatch = from_str(raw.as_str())?;
    Ok(b)
}
