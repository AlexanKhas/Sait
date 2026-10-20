/* ==================================
   LENIS + GSAP SYNC (IMPORTANT)
================================== */

const lenis = new Lenis({
  duration: 1.1,
  smooth: true,
  smoothTouch: false,
  wheelMultiplier: 1
});

function raf(time) {
  lenis.raf(time);
  requestAnimationFrame(raf);
}

requestAnimationFrame(raf);


/* Sync Lenis with GSAP */
lenis.on("scroll", ScrollTrigger.update);

gsap.ticker.add(time => {
  lenis.raf(time * 1000);
});

gsap.ticker.lagSmoothing(0);


/* ==================================
   FIRST SCREEN REVEAL
================================== */

function revealAboveFold() {
  document.querySelectorAll(
    ".fade-up, .card, .feature-img, .feature-text"
  ).forEach(el => {
    const rect = el.getBoundingClientRect();

    if (rect.top < window.innerHeight - 80) {
      el.classList.add("show");
    }
  });
}


/* ==================================
   INTERSECTION OBSERVER
================================== */

const observer = new IntersectionObserver(
  entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("show");

        /* Animate only once */
        observer.unobserve(entry.target);
      }
    });
  },
  {
    threshold: 0.15,
    rootMargin: "0px 0px -80px 0px"
  }
);

function initObserver() {
  document.querySelectorAll(
    ".fade-up, .card, .feature-img, .feature-text"
  ).forEach(el => {
    if (!el.classList.contains("show")) {
      observer.observe(el);
    }
  });
}


/* ==================================
   HERO INIT
================================== */

function initHero() {
  const hero = document.querySelector(".hero-block");

  if (!hero) return;

  setTimeout(() => {
    hero.classList.add("show");
  }, 200);
}


/* ==================================
   GSAP SCROLL EFFECTS
================================== */

gsap.registerPlugin(ScrollTrigger);


/* Hero zoom */
gsap.to(".hero-block", {
  scale: 1.05,
  ease: "none",
  scrollTrigger: {
    trigger: ".hero-block",
    start: "top top",
    end: "bottom top",
    scrub: true
  }
});


/* Feature parallax */
gsap.utils.toArray(".feature-img").forEach(img => {
  gsap.to(img, {
    y: -90,
    ease: "none",
    scrollTrigger: {
      trigger: img,
      start: "top bottom",
      end: "bottom top",
      scrub: true
    }
  });
});


/* Section entrance */
gsap.utils.toArray(".section").forEach(section => {
  gsap.fromTo(
    section,
    { y: 60, opacity: 0 },
    {
      y: 0,
      opacity: 1,
      duration: 1,
      scrollTrigger: {
        trigger: section,
        start: "top 85%"
      }
    }
  );
});


/* Cards stagger */
gsap.utils.toArray(".cards").forEach(cards => {
  const items = cards.querySelectorAll(".card");

  gsap.from(items, {
    y: 50,
    opacity: 0,
    stagger: 0.15,
    duration: 0.8,
    scrollTrigger: {
      trigger: cards,
      start: "top 80%"
    }
  });
});


/* ==================================
   HEADER AUTO HIDE
================================== */

let last = 0;
const header = document.querySelector("header");

window.addEventListener("scroll", () => {
  const now = window.scrollY;

  if (!header) return;

  if (now > last && now > 150) {
    header.style.transform = "translateY(-110%)";
  } else {
    header.style.transform = "translateY(0)";
  }

  last = now;
});


/* ==================================
   INIT
================================== */

window.addEventListener("load", () => {
  initHero();

  revealAboveFold();

  initObserver();

  ScrollTrigger.refresh();
});


window.addEventListener("resize", () => {
  ScrollTrigger.refresh();
});
/* ===============================
   HERO CINEMA INTRO
=============================== */

gsap.from(".hero-content h1", {
  y: 80,
  opacity: 0,
  duration: 1.2,
  ease: "power4.out",
  delay: 0.3
});

gsap.from(".hero-content p", {
  y: 60,
  opacity: 0,
  duration: 1,
  ease: "power3.out",
  delay: 0.6
});

gsap.from(".hero-content .btn-main", {
  y: 40,
  opacity: 0,
  duration: 0.8,
  ease: "power2.out",
  delay: 0.9
});
/* ===============================
   PRELOADER + PAGE TRANSITIONS
=============================== */

window.addEventListener("load", () => {

  const preloader = document.getElementById("preloader");

  if (preloader) {
    setTimeout(() => {
      preloader.classList.add("hidden");
    }, 700);
  }

});


/* Page transition */

document.querySelectorAll("a").forEach(link => {

  if (
    link.href &&
    !link.href.includes("#") &&
    !link.target &&
    !link.classList.contains("no-transition")
  ) {

    link.addEventListener("click", e => {

      e.preventDefault();

      const transition = document.getElementById("page-transition");

      if (!transition) return;

      transition.classList.add("active");

      setTimeout(() => {
        window.location = link.href;
      }, 500);

    });

  }

});
