(() => {
  const canvas = document.querySelector('[data-neural-network]');
  const context = canvas?.getContext('2d');
  if (!context) return;

  const section = canvas.parentElement;
  const fixed = canvas.dataset.fixed === 'true';
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const far = 5.5;
  const near = .35;
  let points = [];
  let frame;
  let width = 0;
  let height = 0;
  let focal = 0;

  function resize() {
    const ratio = Math.min(window.devicePixelRatio || 1, 2);
    width = fixed ? window.innerWidth : section.clientWidth;
    height = fixed ? window.innerHeight : section.clientHeight;
    focal = Math.min(width, height) * .72;
    canvas.width = Math.round(width * ratio);
    canvas.height = Math.round(height * ratio);
    context.setTransform(ratio, 0, 0, ratio, 0, 0);
    const count = Math.min(150, Math.max(70, Math.round(width * height / 5200)));
    points = Array.from({ length: count }, () => ({
      x: (Math.random() * 2 - 1) * width / focal * far * .62,
      y: (Math.random() * 2 - 1) * height / focal * far * .62,
      z: near + Math.random() * (far - near),
      speed: .008 + Math.random() * .012
    }));
    draw();
  }

  function draw() {
    context.clearRect(0, 0, width, height);
    const centerX = width / 2;
    const centerY = height / 2;
    const visible = [];

    for (const point of points) {
      if (!reducedMotion) point.z -= point.speed;
      if (point.z <= near) {
        point.x = (Math.random() * 2 - 1) * width / focal * far * .62;
        point.y = (Math.random() * 2 - 1) * height / focal * far * .62;
        point.z = far;
      }
      const scale = focal / point.z;
      const x = centerX + point.x * scale;
      const y = centerY + point.y * scale;
      const size = Math.min(5, Math.max(.65, 1.9 / point.z));
      if (x > -30 && x < width + 30 && y > -30 && y < height + 30) visible.push({ point, x, y, size });
    }

    const linkDistance = 1.45;
    for (let i = 0; i < visible.length; i++) {
      const a = visible[i];
      for (let j = i + 1; j < visible.length; j++) {
        const b = visible[j];
        const distance = Math.hypot(a.point.x - b.point.x, a.point.y - b.point.y, a.point.z - b.point.z);
        if (distance < linkDistance) {
          const depth = Math.min(a.point.z, b.point.z);
          context.strokeStyle = `rgba(104, 193, 255, ${(1 - distance / linkDistance) * Math.min(.36, .18 / depth)})`;
          context.lineWidth = Math.min(1.1, .45 + .35 / depth);
          context.beginPath();
          context.moveTo(a.x, a.y);
          context.lineTo(b.x, b.y);
          context.stroke();
        }
      }
    }

    for (const node of visible) {
      const alpha = Math.min(.9, .34 + .34 / node.point.z);
      if (!reducedMotion && node.point.z < 1.1) {
        context.strokeStyle = `rgba(106, 220, 255, ${alpha * .22})`;
        context.lineWidth = node.size * .75;
        context.beginPath();
        context.moveTo(centerX + (node.x - centerX) * .9, centerY + (node.y - centerY) * .9);
        context.lineTo(node.x, node.y);
        context.stroke();
      }
      context.fillStyle = `rgba(151, 244, 231, ${alpha})`;
      context.shadowColor = '#55cfff';
      context.shadowBlur = node.size * 3;
      context.beginPath();
      context.arc(node.x, node.y, node.size, 0, Math.PI * 2);
      context.fill();
      context.shadowBlur = 0;
    }

    if (!reducedMotion && !document.hidden) frame = requestAnimationFrame(draw);
  }

  resize();
  window.addEventListener('resize', () => {
    cancelAnimationFrame(frame);
    resize();
  }, { passive: true });
  document.addEventListener('visibilitychange', () => {
    cancelAnimationFrame(frame);
    if (!document.hidden && !reducedMotion) frame = requestAnimationFrame(draw);
  });
})();
