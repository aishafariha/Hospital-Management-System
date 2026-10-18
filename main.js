/* Fariha Healthcare — front-end interactions (vanilla JS, no build step) */
document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Theme toggle (light/dark) ---------- */
  const root = document.documentElement;
  const toggleBtn = document.getElementById('theme-toggle');
  const saved = localStorage.getItem('fariha-theme');
  if (saved) root.setAttribute('data-theme', saved);

  toggleBtn?.addEventListener('click', () => {
    const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    localStorage.setItem('fariha-theme', next);
    toggleBtn.textContent = next === 'dark' ? '☀️' : '🌙';
  });
  if (toggleBtn) toggleBtn.textContent = root.getAttribute('data-theme') === 'dark' ? '☀️' : '🌙';

  /* ---------- Scroll reveal ---------- */
  const revealEls = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });
  revealEls.forEach(el => io.observe(el));

  /* ---------- Subtle 3D tilt on glass cards ---------- */
  document.querySelectorAll('.tilt').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      card.style.transform = `perspective(700px) rotateX(${(-y * 8).toFixed(2)}deg) rotateY(${(x * 8).toFixed(2)}deg) translateY(-4px)`;
    });
    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
  });

  /* ---------- Hero canvas: synaptic / cell network background ---------- */
  const canvas = document.getElementById('hero-canvas');
  if (canvas) {
    const ctx = canvas.getContext('2d');
    let w, h, nodes;
    const NODE_COUNT = 46;

    function resize() {
      w = canvas.width = canvas.offsetWidth;
      h = canvas.height = canvas.offsetHeight;
    }
    function isDark() { return root.getAttribute('data-theme') === 'dark'; }

    function initNodes() {
      nodes = Array.from({ length: NODE_COUNT }, () => ({
        x: Math.random() * w,
        y: Math.random() * h,
        vx: (Math.random() - 0.5) * 0.35,
        vy: (Math.random() - 0.5) * 0.35,
        r: Math.random() * 2 + 1.2
      }));
    }

    function step() {
      ctx.clearRect(0, 0, w, h);
      const lineColor = isDark() ? 'rgba(232,185,78,' : 'rgba(14,124,123,';
      const dotColor = isDark() ? '#E8B94E' : '#0E7C7B';

      nodes.forEach(n => {
        n.x += n.vx; n.y += n.vy;
        if (n.x < 0 || n.x > w) n.vx *= -1;
        if (n.y < 0 || n.y > h) n.vy *= -1;
      });

      for (let i = 0; i < nodes.length; i++) {
        for (let j = i + 1; j < nodes.length; j++) {
          const a = nodes[i], b = nodes[j];
          const dist = Math.hypot(a.x - b.x, a.y - b.y);
          if (dist < 130) {
            ctx.strokeStyle = lineColor + (1 - dist / 130) * 0.35 + ')';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(b.x, b.y);
            ctx.stroke();
          }
        }
      }
      nodes.forEach(n => {
        ctx.beginPath();
        ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
        ctx.fillStyle = dotColor;
        ctx.fill();
      });

      requestAnimationFrame(step);
    }

    resize();
    initNodes();
    step();
    window.addEventListener('resize', () => { resize(); initNodes(); });
  }

  /* ---------- Comment form star rating picker ---------- */
  const starInputs = document.querySelectorAll('.star-pick');
  starInputs.forEach(star => {
    star.addEventListener('click', () => {
      const value = star.dataset.value;
      document.getElementById('rating-field').value = value;
      starInputs.forEach(s => s.classList.toggle('active', s.dataset.value <= value));
    });
  });
});
