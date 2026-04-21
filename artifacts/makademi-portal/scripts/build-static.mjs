import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const src = path.resolve(__dirname, "..", "..", "..", "makademi-website");
const dst = path.resolve(__dirname, "..", "dist", "public");

if (!fs.existsSync(src) || !fs.statSync(src).isDirectory()) {
  console.error(`[build-static] source directory not found: ${src}`);
  process.exit(1);
}

fs.rmSync(dst, { recursive: true, force: true });
fs.mkdirSync(dst, { recursive: true });
fs.cpSync(src, dst, { recursive: true });

console.log(`[build-static] copied ${src} -> ${dst}`);
