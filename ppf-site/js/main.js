/* ════════════════════════════════════════════
   KICK DETAILING — interacties & animaties
   GSAP + ScrollTrigger + Lenis
   ════════════════════════════════════════════ */

gsap.registerPlugin(ScrollTrigger);

const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

/* ── Plates: foto-placeholders opbouwen ─────
   Moody studio-gradients met sheen, grain,
   carrosserie-curves en mono-label.          */
const PLATE_TONES = {
  a: { from: "#2a2622", to: "#0a0908", angle: 135, sheen: "rgba(214,189,140,0.10)" },
  b: { from: "#1d2126", to: "#08090b", angle: 160, sheen: "rgba(180,200,220,0.08)" },
  c: { from: "#231a14", to: "#0b0807", angle: 110, sheen: "rgba(214,189,140,0.14)" },
  d: { from: "#1a1a1c", to: "#070708", angle: 200, sheen: "rgba(255,255,255,0.06)" },
};

document.querySelectorAll(".plate").forEach((plate) => {
  const t = PLATE_TONES[plate.dataset.tone] || PLATE_TONES.a;

  const base = document.createElement("div");
  base.className = "plate__base";
  base.style.background = `linear-gradient(${t.angle}deg, ${t.from} 0%, ${t.to} 100%)`;
  plate.appendChild(base);

  const sheen = document.createElement("div");
  sheen.className = "plate__sheen";
  sheen.style.background = `radial-gradient(60% 50% at 70% 20%, ${t.sheen}, transparent 70%)`;
  plate.appendChild(sheen);

  const grain = document.createElement("div");
  grain.className = "plate__grain";
  plate.appendChild(grain);

  const curves = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  curves.setAttribute("class", "plate__curves");
  curves.setAttribute("viewBox", "0 0 400 300");
  curves.setAttribute("preserveAspectRatio", "none");
  curves.innerHTML =
    '<path d="M -20 220 Q 180 80 420 180" stroke="rgba(255,255,255,0.18)" stroke-width="1" fill="none"/>' +
    '<path d="M -20 260 Q 180 140 420 230" stroke="rgba(255,255,255,0.08)" stroke-width="1" fill="none"/>';
  plate.appendChild(curves);

  if (plate.dataset.overlay !== "false") {
    const overlay = document.createElement("div");
    overlay.className = "plate__overlay";
    plate.appendChild(overlay);
  }

  if (plate.dataset.label) {
    const label = document.createElement("div");
    label.className = "plate__label";
    label.innerHTML = `<span>${plate.dataset.label}</span>`;
    plate.appendChild(label);
  }
});

/* ── Smooth scroll (Lenis) ─────────────────── */
const lenis = new Lenis({ lerp: 0.11, smoothWheel: true });
lenis.on("scroll", ScrollTrigger.update);
gsap.ticker.add((time) => lenis.raf(time * 1000));
gsap.ticker.lagSmoothing(0);

document.querySelectorAll('a[href^="#"]').forEach((link) => {
  link.addEventListener("click", (e) => {
    const target = document.querySelector(link.getAttribute("href"));
    if (!target) return;
    e.preventDefault();
    closeMenu();
    lenis.scrollTo(target, { offset: -72, duration: 1.2 });
  });
});

/* ── Header: achtergrond na scroll ─────────── */
const header = document.getElementById("header");
const onScroll = () => header.classList.toggle("is-scrolled", window.scrollY > 24);
window.addEventListener("scroll", onScroll, { passive: true });
onScroll();

/* ── Mobiel menu ───────────────────────────── */
const burger = document.getElementById("burger");
const mobileMenu = document.getElementById("mobileMenu");

function closeMenu() {
  burger.classList.remove("is-open");
  mobileMenu.classList.remove("is-open");
}

burger.addEventListener("click", () => {
  burger.classList.toggle("is-open");
  mobileMenu.classList.toggle("is-open");
});

/* ── Hero: rustige intro ───────────────────── */
const heroIntro = gsap.timeline({ defaults: { ease: "power3.out" } });
heroIntro
  .from(".hero__content .eyebrow", { y: 18, opacity: 0, duration: 0.8 }, 0.15)
  .from(".hero__title", { y: 28, opacity: 0, duration: 1 }, 0.25)
  .from(".hero__sub", { y: 22, opacity: 0, duration: 0.9 }, 0.4)
  .from(".hero__ctas", { y: 18, opacity: 0, duration: 0.8 }, 0.55)
  .from(".hero__trust > div", { y: 14, opacity: 0, stagger: 0.07, duration: 0.6 }, 0.7)
  .from(".hero__reel, .hero__meta, .hero__scrollcue", { opacity: 0, duration: 0.8 }, 0.9)
  .from(".header", { y: -16, opacity: 0, duration: 0.7 }, 0.3);

/* subtiele parallax op de hero-plate */
gsap.to("#heroPlate", {
  yPercent: 10,
  ease: "none",
  scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true },
});

/* ── Reveals per sectie ────────────────────── */
document.querySelectorAll(".reveal").forEach((el) => {
  gsap.to(el, {
    y: 0,
    opacity: 1,
    duration: 0.9,
    ease: "power3.out",
    scrollTrigger: { trigger: el, start: "top 85%" },
  });
});

/* cards binnen grids licht gestaffeld */
[
  [".services__grid", ".service-card"],
  [".why__grid", ".why-card"],
  [".process__grid", ".process-step"],
  [".business__grid", ".biz-card"],
].forEach(([grid, card]) => {
  const cards = document.querySelectorAll(`${grid} ${card}`);
  if (!cards.length) return;
  gsap.from(cards, {
    y: 20,
    opacity: 0,
    stagger: 0.08,
    duration: 0.7,
    ease: "power3.out",
    scrollTrigger: { trigger: grid, start: "top 82%" },
  });
});

/* ── Intro-stats: tellers ──────────────────── */
document.querySelectorAll(".intro__stat-n[data-count]").forEach((el) => {
  const target = parseInt(el.dataset.count, 10);
  const suffix = el.dataset.suffix || "";
  const obj = { value: 0 };
  gsap.to(obj, {
    value: target,
    duration: 1.6,
    ease: "power2.out",
    onUpdate: () => { el.textContent = Math.round(obj.value) + suffix; },
    scrollTrigger: { trigger: el, start: "top 88%" },
  });
});

/* ── Projecten: filters ────────────────────── */
const filterButtons = document.querySelectorAll("#projectFilters .filter");
const projectCards = document.querySelectorAll("#projectGrid .project-card");

filterButtons.forEach((btn) => {
  btn.addEventListener("click", () => {
    filterButtons.forEach((b) => b.classList.toggle("is-active", b === btn));
    const filter = btn.dataset.filter;

    const show = [];
    const hide = [];
    projectCards.forEach((card) => {
      (filter === "Alles" || card.dataset.cat === filter ? show : hide).push(card);
    });

    const applyFilter = () => {
      hide.forEach((c) => (c.style.display = "none"));
      show.forEach((c) => (c.style.display = ""));
      gsap.fromTo(show,
        { opacity: 0, y: 16, scale: 0.99 },
        { opacity: 1, y: 0, scale: 1, stagger: 0.06, duration: 0.45, ease: "power3.out" }
      );
      ScrollTrigger.refresh();
    };

    if (hide.length) {
      gsap.to(hide, { opacity: 0, scale: 0.97, duration: 0.25, ease: "power2.in", onComplete: applyFilter });
    } else {
      applyFilter();
    }
  });
});

/* ── Jaartal footer ────────────────────────── */
document.getElementById("year").textContent = new Date().getFullYear();

/* ── Reduced motion ────────────────────────── */
if (prefersReducedMotion) {
  gsap.globalTimeline.timeScale(100);
}
