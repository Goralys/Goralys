use goralys_template_renderer::types::GResult;
use goralys_template_renderer::{build_metadata, render_overlay};
use std::env;
use std::process::exit;

fn main() -> GResult<()> {
    // small cli feature

    let show_available = || {
        println!("Available commands:");
        println!("   - build: constructs the metadata for the template");
        println!("   - render: renders the jobs");
    };

    let cmd = env::args().nth(1).unwrap_or_else(|| {
        eprintln!(
            "usage: {} <command> <path>",
            env::args().next().unwrap_or_default()
        );
        show_available();
        exit(1);
    });

    let path = env::args().nth(2).unwrap_or_else(|| {
        eprintln!(
            "usage: {} <cmd> <path>",
            env::args().next().unwrap_or_default()
        );
        show_available();
        exit(2);
    });

    if cmd == "build" {
        build_metadata(path.clone())
    } else if cmd == "render" {
        render_overlay(path)
    } else {
        eprintln!("Unknown command {}", cmd);
        show_available();
        exit(1);
    }
}
