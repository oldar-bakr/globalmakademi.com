import { defineConfig, type Plugin } from "vite";
import fs from "fs";
import path from "path";
import runtimeErrorOverlay from "@replit/vite-plugin-runtime-error-modal";

const rawPort = process.env.PORT;

if (!rawPort) {
  throw new Error(
    "PORT environment variable is required but was not provided.",
  );
}

const port = Number(rawPort);

if (Number.isNaN(port) || port <= 0) {
  throw new Error(`Invalid PORT value: "${rawPort}"`);
}

const basePath = process.env.BASE_PATH;

if (!basePath) {
  throw new Error(
    "BASE_PATH environment variable is required but was not provided.",
  );
}

const staticRoot = path.resolve(
  import.meta.dirname,
  "..",
  "..",
  "makademi-website",
);

const mimeTypes: Record<string, string> = {
  ".html": "text/html; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".js": "text/javascript; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".svg": "image/svg+xml",
  ".png": "image/png",
  ".jpg": "image/jpeg",
  ".jpeg": "image/jpeg",
  ".gif": "image/gif",
  ".webp": "image/webp",
  ".ico": "image/x-icon",
  ".txt": "text/plain; charset=utf-8",
};

function sendFile(res: any, filePath: string, status = 200) {
  const ext = path.extname(filePath).toLowerCase();
  const type = mimeTypes[ext] || "application/octet-stream";
  res.statusCode = status;
  res.setHeader("Content-Type", type);
  res.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
  fs.createReadStream(filePath).pipe(res);
}

function safeResolve(rel: string): string | null {
  const candidate = path.resolve(staticRoot, "." + rel);
  const relative = path.relative(staticRoot, candidate);
  if (
    relative === "" ||
    (!relative.startsWith("..") && !path.isAbsolute(relative))
  ) {
    return candidate;
  }
  return null;
}

function makademiStaticPlugin(): Plugin {
  return {
    name: "makademi-static",
    configureServer(server) {
      server.middlewares.use((req, res, next) => {
        if (!req.url || (req.method !== "GET" && req.method !== "HEAD")) {
          return next();
        }
        const url = new URL(req.url, "http://localhost");
        let pathname: string;
        try {
          pathname = decodeURIComponent(url.pathname);
        } catch {
          return next();
        }
        if (pathname.includes("\0")) return next();

        if (pathname.startsWith("/@") || pathname.startsWith("/__vite")) {
          return next();
        }

        if (pathname === "/") {
          const indexPath = safeResolve("/index.html");
          if (indexPath && fs.existsSync(indexPath)) {
            return sendFile(res, indexPath);
          }
          return next();
        }

        if (pathname.endsWith("/")) {
          const trimmed = pathname.slice(0, -1);
          const htmlPath = safeResolve(trimmed + ".html");
          if (htmlPath && fs.existsSync(htmlPath)) {
            res.statusCode = 302;
            res.setHeader("Location", trimmed + ".html");
            return res.end();
          }
          const indexPath = safeResolve(pathname + "index.html");
          if (indexPath && fs.existsSync(indexPath)) {
            return sendFile(res, indexPath);
          }
          return next();
        }

        if (!path.extname(pathname)) {
          const htmlPath = safeResolve(pathname + ".html");
          if (htmlPath && fs.existsSync(htmlPath)) {
            return sendFile(res, htmlPath);
          }
        }

        const filePath = safeResolve(pathname);
        if (
          filePath &&
          fs.existsSync(filePath) &&
          fs.statSync(filePath).isFile()
        ) {
          return sendFile(res, filePath);
        }

        const fallback = safeResolve("/404.html");
        if (fallback && fs.existsSync(fallback)) {
          return sendFile(res, fallback, 404);
        }
        next();
      });
    },
  };
}

export default defineConfig({
  base: basePath,
  plugins: [
    makademiStaticPlugin(),
    runtimeErrorOverlay(),
    ...(process.env.NODE_ENV !== "production" &&
    process.env.REPL_ID !== undefined
      ? [
          await import("@replit/vite-plugin-cartographer").then((m) =>
            m.cartographer({
              root: path.resolve(import.meta.dirname, ".."),
            }),
          ),
          await import("@replit/vite-plugin-dev-banner").then((m) =>
            m.devBanner(),
          ),
        ]
      : []),
  ],
  publicDir: staticRoot,
  root: path.resolve(import.meta.dirname),
  build: {
    outDir: path.resolve(import.meta.dirname, "dist/public"),
    emptyOutDir: true,
  },
  server: {
    port,
    strictPort: true,
    host: "0.0.0.0",
    allowedHosts: true,
    fs: {
      strict: false,
      allow: [path.resolve(import.meta.dirname, "..", "..")],
    },
  },
  preview: {
    port,
    host: "0.0.0.0",
    allowedHosts: true,
  },
});
