/* =========================================================
   UKM Fotografi Universitas Pancasila — Main Script
   Konten halaman dirender oleh Laravel (resources/views/home.blade.php).
   ========================================================= */

const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/* ---------- GALERI: FILTER + LIGHTBOX ---------- */
// dipakai carousel nominasi untuk membuka foto di lightbox yang sama dengan galeri
let openLightbox = null;

function initGallery() {
  const items = $$(".gallery-item");
  if (!$("#lightbox")) return;

  const lightbox = $("#lightbox");
  const csrf = $('meta[name="csrf-token"]').content;
  const viewed = new Set();
  let visible = items;
  // daftar foto yang sedang dijelajahi di lightbox (galeri atau nominasi)
  let list = items;
  let current = 0;

  $("#filterBar")?.addEventListener("click", (e) => {
    const chip = e.target.closest(".chip");
    if (!chip) return;
    $$(".chip").forEach((c) => c.classList.toggle("active", c === chip));
    const f = chip.dataset.filter;
    items.forEach((it) => it.classList.toggle("hide", f !== "semua" && it.dataset.kategori !== f));
    visible = items.filter((it) => !it.classList.contains("hide"));
  });

  const setLink = (el, url) => {
    el.hidden = !url;
    if (url) el.href = url;
  };

  // catat jumlah dilihat (sekali per foto per kunjungan) untuk laporan insight karya
  const recordView = (item) => {
    if (viewed.has(item) || !item.dataset.viewUrl) return;
    viewed.add(item);
    fetch(item.dataset.viewUrl, { method: "POST", headers: { "X-CSRF-TOKEN": csrf } }).catch(() => {});
  };

  const show = (i) => {
    current = (i + list.length) % list.length;
    const d = list[current].dataset;
    $("#lbImg").src = d.src;
    $("#lbImg").alt = d.title;
    $("#lbTitle").textContent = d.title;
    $("#lbAuthor").textContent = `oleh ${d.author}`;
    $("#lbCaption").textContent = d.caption || "";
    $("#lbExposure").textContent = d.exposure || "";
    $("#lbExposureNotes").textContent = d.exposureNotes || "";
    setLink($("#lbInstagram"), d.instagram);
    setLink($("#lbTiktok"), d.tiktok);
    recordView(list[current]);
  };

  const open = (item, from = visible) => {
    list = from;
    show(list.indexOf(item));
    lightbox.classList.add("open");
    lightbox.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    $("#lbClose").focus();
  };

  const close = () => {
    lightbox.classList.remove("open");
    lightbox.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  };

  openLightbox = open;
  items.forEach((it) => {
    it.addEventListener("click", () => open(it));
    it.addEventListener("keydown", (e) => e.key === "Enter" && open(it));
  });

  $("#lbClose").addEventListener("click", close);
  $("#lbPrev").addEventListener("click", () => show(current - 1));
  $("#lbNext").addEventListener("click", () => show(current + 1));
  lightbox.addEventListener("click", (e) => e.target === lightbox && close());

  document.addEventListener("keydown", (e) => {
    if (!lightbox.classList.contains("open")) return;
    if (e.key === "Escape") close();
    if (e.key === "ArrowLeft") show(current - 1);
    if (e.key === "ArrowRight") show(current + 1);
  });
}

/* ---------- NOMINASI FOTO MINGGUAN: carousel yang bergeser halus ---------- */
function initCarousel() {
  const root = $("[data-carousel]");
  if (!root) return;
  const track = $(".nominee-track", root);
  const slides = $$(".nominee-slide", root);
  const dots = $$(".nominee-dot", root);
  const reduce = matchMedia("(prefers-reduced-motion: reduce)").matches;
  const delay = parseFloat(getComputedStyle(root).getPropertyValue("--slide-ms")) || 5000;
  let index = 0;
  let timer = null;
  let hovered = false;
  let moved = false;

  const autoplay = slides.length > 1 && !reduce;
  root.classList.toggle("static", !autoplay);

  const schedule = () => {
    clearTimeout(timer);
    if (autoplay && !root.classList.contains("paused")) timer = setTimeout(() => go(index + 1), delay);
  };

  const go = (n) => {
    index = (n + slides.length) % slides.length;
    track.style.transform = `translateX(-${index * 100}%)`;
    slides.forEach((s, i) => {
      s.classList.toggle("is-active", i === index);
      s.setAttribute("aria-hidden", String(i !== index));
      s.tabIndex = i === index ? 0 : -1;
    });
    dots.forEach((d, i) => {
      d.classList.toggle("active", i === index);
      d.setAttribute("aria-current", String(i === index));
      // ulangi animasi garis waktu pada titik yang aktif
      const bar = d.firstElementChild;
      bar.style.animation = "none";
      void bar.offsetWidth;
      bar.style.animation = "";
    });
    schedule();
  };

  const setPaused = (paused) => {
    root.classList.toggle("paused", paused);
    paused ? clearTimeout(timer) : schedule();
  };

  $("[data-prev]", root)?.addEventListener("click", () => go(index - 1));
  $("[data-next]", root)?.addEventListener("click", () => go(index + 1));
  dots.forEach((d) => d.addEventListener("click", () => go(Number(d.dataset.dot))));

  // berhenti sebentar saat diarahkan kursor, difokuskan, atau tab tidak terlihat
  root.addEventListener("mouseenter", () => { hovered = true; setPaused(true); });
  root.addEventListener("mouseleave", () => { hovered = false; setPaused(root.contains(document.activeElement)); });
  root.addEventListener("focusin", () => setPaused(true));
  root.addEventListener("focusout", (e) => !root.contains(e.relatedTarget) && setPaused(hovered));
  document.addEventListener("visibilitychange", () => setPaused(document.hidden || hovered));

  root.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") go(index - 1);
    if (e.key === "ArrowRight") go(index + 1);
    if (e.key === "Enter" && e.target.classList.contains("nominee-slide")) openLightbox?.(e.target, slides);
  });

  // geser dengan jari / mouse
  let startX = 0;
  let dragging = false;
  const viewport = $(".nominee-viewport", root);
  viewport.addEventListener("pointerdown", (e) => {
    if (slides.length < 2) return;
    dragging = true;
    moved = false;
    startX = e.clientX;
    track.classList.add("dragging");
    clearTimeout(timer);
  });
  window.addEventListener("pointermove", (e) => {
    if (!dragging) return;
    const dx = e.clientX - startX;
    if (Math.abs(dx) > 6) moved = true;
    track.style.transform = `translateX(calc(-${index * 100}% + ${dx}px))`;
  });
  const endDrag = (e) => {
    if (!dragging) return;
    dragging = false;
    track.classList.remove("dragging");
    const dx = e.clientX - startX;
    go(Math.abs(dx) > viewport.clientWidth * 0.15 ? index - Math.sign(dx) : index);
  };
  window.addEventListener("pointerup", endDrag);
  window.addEventListener("pointercancel", endDrag);

  slides.forEach((s) => s.addEventListener("click", () => {
    if (!moved) openLightbox?.(s, slides);
  }));

  go(0);
}

/* ---------- BAGIAN HALAMAN (HP: daftar tombol buka-tutup) ---------- */
function initSections() {
  const sections = $$(".section").filter((s) => $(".section-toggle", s));

  const setOpen = (section, open) => {
    section.classList.toggle("is-open", open);
    $(".section-toggle", section).setAttribute("aria-expanded", String(open));
  };

  sections.forEach((section) => {
    setOpen(section, section.classList.contains("is-open"));
    $(".section-toggle", section).addEventListener("click", () => {
      setOpen(section, !section.classList.contains("is-open"));
    });
  });

  // link ke suatu bagian (menu, tombol hero, #alamat di URL) ikut membuka bagian itu
  const openFromHash = (hash) => {
    const target = hash && sections.find((s) => `#${s.id}` === hash);
    if (target) setOpen(target, true);
  };

  $$('a[href^="#"]').forEach((a) => a.addEventListener("click", () => openFromHash(a.getAttribute("href"))));
  openFromHash(location.hash);
}

/* ---------- HERO: bubble di belakang mockup ikut bergerak saat di-scroll ---------- */
function initBlobs() {
  const blobs = $(".blobs");
  if (!blobs) return;
  const hero = blobs.parentElement;
  const mobile = matchMedia("(max-width: 900px)");

  // di HP bubble menempel di layar mulai dari posisi mockup, jadi ikut turun saat di-scroll
  const place = () => {
    blobs.classList.toggle("follow", mobile.matches);
    if (!mobile.matches) { blobs.removeAttribute("style"); return; }
    // posisi di dokumen lewat offset, supaya tidak terpengaruh transform animasi
    let top = 0, left = 0;
    for (let el = hero; el; el = el.offsetParent) { top += el.offsetTop; left += el.offsetLeft; }
    Object.assign(blobs.style, { top: `${top}px`, left: `${left}px`, width: `${hero.offsetWidth}px`, height: `${hero.offsetHeight}px` });
  };
  place();
  window.addEventListener("resize", place);
  window.addEventListener("load", place);

  if (matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  let ticking = false;
  const update = () => {
    blobs.style.setProperty("--sy", `${Math.min(window.scrollY, 320)}px`);
    ticking = false;
  };
  window.addEventListener("scroll", () => {
    if (!ticking) { ticking = true; requestAnimationFrame(update); }
  }, { passive: true });
  update();
}

/* ---------- HEADER: bayangan saat di-scroll + menu aktif ---------- */
function initTopbar() {
  const topbar = $(".topbar");
  const onScroll = () => topbar.classList.toggle("scrolled", window.scrollY > 8);
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  const links = $$(".nav a");
  if (!("IntersectionObserver" in window) || !links.length) return;
  const spy = new IntersectionObserver(
    (entries) => entries.forEach((e) => {
      if (e.isIntersecting) links.forEach((a) => a.classList.toggle("active", a.getAttribute("href") === `#${e.target.id}`));
    }),
    { rootMargin: "-45% 0px -50% 0px" }
  );
  links.forEach((a) => {
    const target = $(a.getAttribute("href"));
    if (target) spy.observe(target);
  });
}

/* ---------- ANIMASI MUNCUL SAAT DI-SCROLL ---------- */
function initReveal() {
  const items = $$(".reveal");
  if (!("IntersectionObserver" in window)) {
    items.forEach((el) => el.classList.add("in"));
    return;
  }
  const io = new IntersectionObserver(
    (entries) => entries.forEach((e) => {
      if (!e.isIntersecting) return;
      e.target.classList.add("in");
      io.unobserve(e.target);
    }),
    { threshold: 0.12 }
  );
  items.forEach((el) => io.observe(el));
}

document.addEventListener("DOMContentLoaded", () => {
  initSections();
  initTopbar();
  initBlobs();
  initCarousel();
  initReveal();
  initGallery();
});
