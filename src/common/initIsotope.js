const setupIsotope = (Isotope) => {
  const grid = document.querySelectorAll(".gallery");
  if (!grid.length) return;

  let iso;
  grid.forEach((item) => {
    iso = new Isotope(item, {
      itemSelector: ".items",
    });
  });

  const filtersElem = document.querySelector(".filtering");
  if (!filtersElem || !iso) return;

  filtersElem.addEventListener("click", function (event) {
    if (!event.target.matches("span")) {
      return;
    }
    const filterValue = event.target.getAttribute("data-filter");
    iso.arrange({ filter: filterValue });
  });

  const buttonGroups = document.querySelectorAll(".filtering");
  buttonGroups.forEach((buttonGroup) => {
    buttonGroup.addEventListener("click", function (event) {
      if (!event.target.matches("span")) return;
      const active = buttonGroup.querySelector(".active");
      if (active) active.classList.remove("active");
      event.target.classList.add("active");
    });
  });
};

const initIsotope = () => {
  if (typeof window === "undefined") return;

  import("isotope-layout")
    .then((module) => {
      const Isotope = module.default;
      setupIsotope(Isotope);
    })
    .catch(() => {});
};

export default initIsotope;
