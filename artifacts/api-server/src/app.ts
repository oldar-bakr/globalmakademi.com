import express, { type Express } from "express";
import cors from "cors";
import fs from "fs";
import path from "path";
import pinoHttp from "pino-http";
import router from "./routes";
import { logger } from "./lib/logger";

const app: Express = express();

app.use(
  pinoHttp({
    logger,
    serializers: {
      req(req) {
        return {
          id: req.id,
          method: req.method,
          url: req.url?.split("?")[0],
        };
      },
      res(res) {
        return {
          statusCode: res.statusCode,
        };
      },
    },
  }),
);
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.use("/api", router);

const staticRoot = path.resolve(process.cwd(), "../../makademi-website");

app.use((req, res, next) => {
  if ((req.method !== "GET" && req.method !== "HEAD") || req.path.startsWith("/api")) return next();
  if (path.extname(req.path)) return next();
  if (req.path === "/") return next();

  // Handle trailing-slash case: /courses/ -> serve courses.html via redirect
  if (req.path.endsWith("/")) {
    const trimmed = req.path.slice(0, -1);
    const htmlPath = path.join(staticRoot, trimmed + ".html");
    if (htmlPath.startsWith(staticRoot) && fs.existsSync(htmlPath)) {
      return res.redirect(302, trimmed + ".html");
    }
    return next();
  }

  // Normal case: /courses -> serve courses.html
  const htmlPath = path.join(staticRoot, req.path + ".html");
  if (htmlPath.startsWith(staticRoot) && fs.existsSync(htmlPath)) {
    res.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
    res.status(200);
    return res.sendFile(htmlPath);
  }
  next();
});

app.use(
  express.static(staticRoot, {
    extensions: ["html"],
    redirect: false,
    setHeaders: (res) => {
      res.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
    },
  }),
);

app.use((req, res, next) => {
  if (req.method !== "GET" || req.path.startsWith("/api")) return next();
  res.status(404).sendFile(path.join(staticRoot, "404.html"), (err) => {
    if (err) next(err);
  });
});

export default app;
