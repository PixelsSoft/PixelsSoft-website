// Force any leftover wow / noraidus / splitting hide states visible.
if (typeof window !== "undefined") {
  try {
    document.querySelectorAll(".wow, .noraidus .cont, .splitting .char").forEach((el) => {
      el.style.visibility = "visible";
      el.style.opacity = "1";
      el.style.animation = "none";
      el.classList.add("animated");
    });
  } catch (_) {}
}
