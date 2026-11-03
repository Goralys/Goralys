use crate::types::GResult;
use pdfium_render::prelude::Pdfium;

pub fn load_pdfium(path: &String) -> GResult<Pdfium> {
    Ok(Pdfium::new(
        Pdfium::bind_to_library(Pdfium::pdfium_platform_library_name_at_path(path))
            .or_else(|_| Pdfium::bind_to_system_library())?,
    ))
}
