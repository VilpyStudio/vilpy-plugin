/* ════════════════════════════════════════════
   GLOSSWERK — interactions & animations
   GSAP + ScrollTrigger + Lenis
   ════════════════════════════════════════════ */

gsap.registerPlugin(ScrollTrigger);

const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const isTouch = window.matchMedia("(hover: none), (pointer: coarse)").matches;

/* ── Smooth scroll (Lenis) ─────────────────── */
const lenis = new Lenis({ lerp: 0.1, smoothWheel: true });
lenis.on("scroll", ScrollTrigger.update);
gsap.ticker.add((time) => lenis.raf(time * 1000));
gsap.ticker.lagSmoothing(0);

// ankerlinks via Lenis laten lopen
document.querySelectorAll('a[href^="#"]').forEach((link) => {
  link.addEventListener("click", (e) => {
    const target = document.querySelector(link.getAttribute("href"));
    if (!target) return;
    e.preventDefault();
    closeMenu();
    lenis.scrollTo(target, { offset: 0, duration: 1.4 });
  });
});

/* ── Tekst splitsen (chars / words) ────────── */
function splitText(el, mode) {
  const text = el.textContent;
  el.textContent = "";
  el.setAttribute("aria-label", text);
  text.split(" ").forEach((word, i, words) => {
    const wordSpan = document.createElement("span");
    wordSpan.className = "word";
    wordSpan.setAttribute("aria-hidden", "true");
    if (mode === "chars") {
      word.split("").forEach((ch) => {
        const charSpan = document.createElement("span");
        charSpan.className = "char";
        charSpan.textContent = ch;
        wordSpan.appendChild(charSpan);
      });
    } else {
      wordSpan.textContent = word;
    }
    el.appendChild(wordSpan);
    if (i < words.length - 1) el.appendChild(document.createTextNode(" "));
  });
  return el.querySelectorAll(mode === "chars" ? ".char" : ".word");
}

document.querySelectorAll("[data-split]").forEach((el) => splitText(el, el.dataset.split));

/* ── Preloader → hero intro ────────────────── */
const counter = { value: 0 };
const introTl = gsap.timeline({
  defaults: { ease: "power3.out" },
  onComplete: () => ScrollTrigger.refresh(),
});

introTl
  .to(counter, {
    value: 100,
    duration: prefersReducedMotion ? 0.1 : 1.6,
    ease: "power2.inOut",
    onUpdate: () => {
      document.getElementById("preloaderCount").textContent = Math.round(counter.value);
      document.getElementById("preloaderBar").style.width = counter.value + "%";
    },
  })
  .to("#preloader", {
    yPercent: -100,
    duration: 0.9,
    ease: "power4.inOut",
  })
  .set("#preloader", { display: "none" })
  .from("#heroImg", { scale: 1.25, duration: 1.6, ease: "power3.out" }, "-=0.9")
  .from(".hero__kicker .char", { yPercent: 110, opacity: 0, stagger: 0.012, duration: 0.7 }, "-=1.3")
  .from(".hero__title .char", { yPercent: 110, stagger: 0.02, duration: 0.9 }, "-=1.1")
  .from(".hero__sub, .hero__scroll", { y: 24, opacity: 0, stagger: 0.12, duration: 0.8 }, "-=0.6")
  .from(".hero__meta span", { x: 20, opacity: 0, stagger: 0.08, duration: 0.6 }, "-=0.7")
  .from(".nav", { y: -30, opacity: 0, duration: 0.7 }, "-=0.8");

/* ── Hero parallax bij scrollen ────────────── */
gsap.to("#heroImg", {
  yPercent: 18,
  scale: 1.08,
  ease: "none",
  scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom top", scrub: true },
});
gsap.to(".hero__content", {
  yPercent: -12,
  opacity: 0.25,
  ease: "none",
  scrollTrigger: { trigger: ".hero", start: "top top", end: "bottom 35%", scrub: true },
});

/* ── Marquee ───────────────────────────────── */
const marqueeTween = gsap.to("#marqueeTrack", {
  xPercent: -33.333,
  repeat: -1,
  duration: 18,
  ease: "none",
});
// scrollrichting beïnvloedt snelheid
ScrollTrigger.create({
  onUpdate: (self) => {
    gsap.to(marqueeTween, { timeScale: self.direction * (1 + Math.min(Math.abs(self.getVelocity()) / 800, 3)), duration: 0.4 });
  },
});

/* ── Statement: woord-voor-woord scrub ─────── */
const statementWords = splitText(document.getElementById("statementText"), "words");
gsap.to(statementWords, {
  opacity: 1,
  stagger: 0.06,
  ease: "none",
  scrollTrigger: {
    trigger: ".statement",
    start: "top 75%",
    end: "bottom 45%",
    scrub: true,
  },
});

/* ── Sectiekoppen ──────────────────────────── */
document.querySelectorAll(".section-head__title").forEach((title) => {
  gsap.from(title.querySelectorAll(".word"), {
    yPercent: 110,
    opacity: 0,
    stagger: 0.08,
    duration: 0.8,
    ease: "power3.out",
    scrollTrigger: { trigger: title, start: "top 85%" },
  });
});

/* ── Diensten: rijen + zwevende preview ────── */
gsap.from(".service", {
  y: 50,
  opacity: 0,
  stagger: 0.1,
  duration: 0.8,
  ease: "power3.out",
  scrollTrigger: { trigger: ".services__list", start: "top 80%" },
});

const preview = document.getElementById("servicePreview");
const previewImg = document.getElementById("servicePreviewImg");
if (!isTouch && preview) {
  const setX = gsap.quickTo(preview, "x", { duration: 0.5, ease: "power3" });
  const setY = gsap.quickTo(preview, "y", { duration: 0.5, ease: "power3" });
  window.addEventListener("mousemove", (e) => {
    setX(e.clientX + 28);
    setY(e.clientY - preview.offsetHeight / 2);
  });
  document.querySelectorAll(".service").forEach((row) => {
    row.addEventListener("mouseenter", () => {
      previewImg.src = row.dataset.img;
      gsap.to(preview, { opacity: 1, scale: 1, duration: 0.4, ease: "power3.out" });
    });
    row.addEventListener("mouseleave", () => {
      gsap.to(preview, { opacity: 0, scale: 0.85, duration: 0.3, ease: "power3.in" });
    });
  });
}

/* ── Before / after vergelijker ────────────── */
(() => {
  const frame = document.getElementById("compareFrame");
  const after = document.getElementById("compareAfter");
  const handle = document.getElementById("compareHandle");
  if (!frame) return;

  let dragging = false;

  const setPosition = (clientX) => {
    const rect = frame.getBoundingClientRect();
    const pct = gsap.utils.clamp(2, 98, ((clientX - rect.left) / rect.width) * 100);
    after.style.clipPath = `inset(0 0 0 ${pct}%)`;
    handle.style.left = pct + "%";
  };

  frame.addEventListener("pointerdown", (e) => {
    dragging = true;
    frame.setPointerCapture(e.pointerId);
    setPosition(e.clientX);
  });
  frame.addEventListener("pointermove", (e) => dragging && setPosition(e.clientX));
  ["pointerup", "pointercancel"].forEach((evt) =>
    frame.addEventListener(evt, () => (dragging = false))
  );

  // kleine intro-sweep zodra de sectie in beeld komt
  if (!prefersReducedMotion) {
    const sweep = { pct: 50 };
    gsap.fromTo(sweep, { pct: 85 }, {
      pct: 50,
      duration: 1.4,
      ease: "power3.inOut",
      onUpdate: () => {
        after.style.clipPath = `inset(0 0 0 ${sweep.pct}%)`;
        handle.style.left = sweep.pct + "%";
      },
      scrollTrigger: { trigger: frame, start: "top 70%" },
    });
  }
})();

/* ── Werk: horizontale scroll (desktop) ────── */
ScrollTrigger.matchMedia({
  "(min-width: 769px)": () => {
    const track = document.getElementById("workTrack");
    const getDistance = () => track.scrollWidth - window.innerWidth;
    gsap.to(track, {
      x: () => -getDistance(),
      ease: "none",
      scrollTrigger: {
        trigger: "#workPin",
        start: "top top",
        end: () => "+=" + getDistance(),
        pin: true,
        scrub: 1,
        invalidateOnRefresh: true,
      },
    });
  },
  "(max-width: 768px)": () => {
    gsap.utils.toArray(".work__item").forEach((item) => {
      gsap.from(item, {
        y: 60,
        opacity: 0,
        duration: 0.8,
        ease: "power3.out",
        scrollTrigger: { trigger: item, start: "top 88%" },
      });
    });
  },
});

/* ── Proces: stappen in beeld ──────────────── */
gsap.from(".step", {
  y: 60,
  opacity: 0,
  stagger: 0.12,
  duration: 0.9,
  ease: "power3.out",
  scrollTrigger: { trigger: ".process__steps", start: "top 82%" },
});

/* ── Stats: tellers ────────────────────────── */
document.querySelectorAll(".stat__value").forEach((el) => {
  const target = parseFloat(el.dataset.count);
  const isDecimal = String(el.dataset.count).includes(".");
  const obj = { value: 0 };
  gsap.to(obj, {
    value: target,
    duration: 1.8,
    ease: "power2.out",
    onUpdate: () => {
      el.textContent = isDecimal ? obj.value.toFixed(1) : Math.round(obj.value);
    },
    scrollTrigger: { trigger: el, start: "top 88%" },
  });
});

/* ── Contact CTA ───────────────────────────── */
gsap.from(".contact__big .char", {
  yPercent: 110,
  stagger: 0.025,
  duration: 0.8,
  ease: "power3.out",
  scrollTrigger: { trigger: ".contact", start: "top 70%" },
});

/* ── Footer brandtekst ─────────────────────── */
gsap.from(".footer__brand", {
  yPercent: 60,
  ease: "none",
  scrollTrigger: { trigger: ".footer", start: "top bottom", end: "bottom bottom", scrub: true },
});

/* ── Custom cursor ─────────────────────────── */
(() => {
  if (isTouch) return;
  const cursor = document.getElementById("cursor");
  const label = document.getElementById("cursorLabel");
  const cx = gsap.quickTo(cursor, "x", { duration: 0.25, ease: "power3" });
  const cy = gsap.quickTo(cursor, "y", { duration: 0.25, ease: "power3" });
  window.addEventListener("mousemove", (e) => {
    cursor.style.opacity = "1";
    cx(e.clientX);
    cy(e.clientY);
  });

  document.querySelectorAll("[data-cursor], a, button").forEach((el) => {
    el.addEventListener("mouseenter", () => cursor.classList.add("is-hover"));
    el.addEventListener("mouseleave", () => cursor.classList.remove("is-hover"));
  });
  document.querySelectorAll("[data-cursor-label]").forEach((el) => {
    el.addEventListener("mouseenter", () => {
      label.textContent = el.dataset.cursorLabel;
      cursor.classList.add("is-label");
    });
    el.addEventListener("mouseleave", () => cursor.classList.remove("is-label"));
  });
})();

/* ── Magnetische knoppen ───────────────────── */
if (!isTouch) {
  document.querySelectorAll(".btn-magnetic").forEach((btn) => {
    const strength = 0.35;
    btn.addEventListener("mousemove", (e) => {
      const rect = btn.getBoundingClientRect();
      gsap.to(btn, {
        x: (e.clientX - rect.left - rect.width / 2) * strength,
        y: (e.clientY - rect.top - rect.height / 2) * strength,
        duration: 0.4,
        ease: "power3.out",
      });
    });
    btn.addEventListener("mouseleave", () => {
      gsap.to(btn, { x: 0, y: 0, duration: 0.6, ease: "elastic.out(1, 0.4)" });
    });
  });
}

/* ── Mobiel menu ───────────────────────────── */
const burger = document.getElementById("navBurger");
const menu = document.getElementById("menu");
let menuOpen = false;

const menuTl = gsap.timeline({ paused: true })
  .set(menu, { visibility: "visible" })
  .to(menu, { clipPath: "inset(0 0 0% 0)", duration: 0.7, ease: "power4.inOut" })
  .from(".menu__links a", { y: 60, opacity: 0, stagger: 0.07, duration: 0.6, ease: "power3.out" }, "-=0.25")
  .from(".menu__foot", { opacity: 0, duration: 0.4 }, "-=0.3");

function closeMenu() {
  if (!menuOpen) return;
  menuOpen = false;
  burger.classList.remove("is-open");
  menuTl.reverse();
  lenis.start();
}

burger.addEventListener("click", () => {
  menuOpen = !menuOpen;
  burger.classList.toggle("is-open", menuOpen);
  if (menuOpen) { menuTl.play(); lenis.stop(); }
  else { menuTl.reverse(); lenis.start(); }
});

/* ── Reduced motion: alles direct tonen ────── */
if (prefersReducedMotion) {
  gsap.globalTimeline.timeScale(100);
  marqueeTween.pause();
}
