import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { Agentation } from "agentation";

const MOUNT_ID = "__agentation-root";

function mount() {
  if (document.getElementById(MOUNT_ID)) return;
  const host = document.createElement("div");
  host.id = MOUNT_ID;
  document.body.appendChild(host);
  createRoot(host).render(
    <StrictMode>
      <Agentation />
    </StrictMode>,
  );
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", mount, { once: true });
} else {
  mount();
}
