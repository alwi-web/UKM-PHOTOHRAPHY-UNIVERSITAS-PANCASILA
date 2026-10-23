/* =========================================================
   UKM Fotografi Universitas Pancasila — Main Script
   Konten halaman dirender oleh Laravel (resources/views/home.blade.php).
   ========================================================= */

const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/* ---------- GALERI: FILTER + LIGHTBOX ---------- */
function initGallery() {
  const items = $$(".gallery-item");
  if (!items.length) return;

  const lightbox = $("#lightbox");
  const csrf = $('meta[name="csrf-token"]').content;
  const viewed = new Set();
  let visible = items;
  let current = 0;

  $("#filterBar").addEventListener("click", (e) => {
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
    current = (i + visible.length) % visible.length;
    const d = visible[current].dataset;
    $("#lbImg").src = d.src;
    $("#lbImg").alt = d.title;
    $("#lbTitle").textContent = d.title;
    $("#lbAuthor").textContent = `oleh ${d.author}`;
    $("#lbCaption").textContent = d.caption || "";
    $("#lbExposure").textContent = d.exposure || "";
    $("#lbExposureNotes").textContent = d.exposureNotes || "";
    setLink($("#lbInstagram"), d.instagram);
    setLink($("#lbTiktok"), d.tiktok);
    recordView(visible[current]);
  };

  const open = (item) => {
    show(visible.indexOf(item));
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

/* ---------- FORM PENDAFTARAN ---------- */
function initForm() {
  const form = $("#registerForm");
  if (!form) return;

  form.addEventListener("submit", () => {
    const btn = $("button[type=submit]", form);
    btn.disabled = true;
    btn.textContent = "Mengirim...";
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initGallery();
  initForm();
});
