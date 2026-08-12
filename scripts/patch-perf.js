/**
 * Patches third-party CSS for PageSpeed (font-display: swap).
 * Runs on postinstall so npm install doesn't wipe the fix.
 */
const fs = require("fs");
const path = require("path");

const slickTheme = path.join(
  __dirname,
  "..",
  "node_modules",
  "slick-carousel",
  "slick",
  "slick-theme.css"
);

if (fs.existsSync(slickTheme)) {
  let css = fs.readFileSync(slickTheme, "utf8");
  if (!/font-display\s*:\s*swap/.test(css)) {
    css = css.replace(/@font-face\s*\{/g, "@font-face{font-display:swap;");
    fs.writeFileSync(slickTheme, css);
    console.log("patched slick-theme.css font-display");
  } else {
    console.log("slick-theme.css already patched");
  }
} else {
  console.log("slick-theme.css not found, skip");
}
