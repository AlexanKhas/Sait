// ================= MENU =================
const menuBtn = document.querySelector(".menu-btn");
const nav = document.querySelector("header nav");
if(menuBtn) menuBtn.onclick = () => nav.classList.toggle("active");

// ================= ALERT AUTO HIDE =================
setTimeout(() => {
    document.querySelectorAll(".alert").forEach(a => {
        a.style.opacity = "0";
        setTimeout(() => a.remove(), 400);
    });
}, 3000);

// ================= CONFIRM =================
document.querySelectorAll("[data-confirm]").forEach(btn => {
    btn.onclick = e => {
        if(!confirm("Вы уверены?")) e.preventDefault();
    };
});

// ================= TABLE SEARCH =================
document.querySelectorAll(".table-search").forEach(input => {
    input.onkeyup = () => {
        let val = input.value.toLowerCase();
        let table = document.getElementById(input.dataset.table);
        table.querySelectorAll("tr").forEach((tr, i) => {
            if(i === 0) return; // заголовок
            tr.style.display = tr.innerText.toLowerCase().includes(val) ? "" : "none";
        });
    };
});

// ================= SCROLL ANIMATION =================
function scrollAppear() {
    const triggerBottom = window.innerHeight * 0.85;

    const fadeEls = document.querySelectorAll('.fade-up');
    fadeEls.forEach(el => {
        const top = el.getBoundingClientRect().top;
        if(top < triggerBottom) el.classList.add('show');
    });

    const hero = document.querySelector('.hero-block');
    if(hero && hero.getBoundingClientRect().top < triggerBottom){
        hero.classList.add('show');
    }

    const cards = document.querySelectorAll('.cards .card');
    if(cards.length){
        cards.forEach(card => {
            const top = card.getBoundingClientRect().top;
            if(top < triggerBottom) card.classList.add('show');
        });
    }

    const featureImg = document.querySelector('.feature-img');
    const featureText = document.querySelector('.feature-text');
    if(featureImg && featureImg.getBoundingClientRect().top < triggerBottom){
        featureImg.classList.add('show');
        if(featureText) featureText.classList.add('show');
    }
}
window.addEventListener('scroll', scrollAppear);
window.addEventListener('load', scrollAppear);

// ================= МОДАЛКА ФИО (для profile.php) =================
const editBtn = document.getElementById('edit-name');
if(editBtn){
    const modal = document.getElementById('nameModal');
    const saveBtn = document.getElementById('saveName');

    editBtn.onclick = () => {
        document.getElementById('firstName').value = '';
        document.getElementById('lastName').value = '';
        document.getElementById('middleName').value = '';
        modal.classList.add('show');
    };

    modal.onclick = e => { if(e.target === modal) modal.classList.remove('show'); };

    saveBtn.addEventListener('click', e => {
        e.preventDefault();
        const first  = document.getElementById('firstName').value.trim();
        const last   = document.getElementById('lastName').value.trim();
        const middle = document.getElementById('middleName').value.trim();

        fetch('update_name.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'first_name=' + encodeURIComponent(first) +
                  '&last_name=' + encodeURIComponent(last) +
                  '&middle_name=' + encodeURIComponent(middle)
        })
        .then(r => r.json())
        .then(res => {
            if(res.success){
                document.getElementById('username').innerText = res.name;
                modal.classList.remove('show');
            } else {
                alert(res.message || 'Ошибка при сохранении имени');
            }
        })
        .catch(err => { console.error(err); alert('Ошибка при сохранении имени'); });
    });
}

/* ===============================
   UI CORE (TOAST + THEME)
================================ */

document.addEventListener("DOMContentLoaded", () => {

    /* ---------- TOAST ---------- */

    window.showToast = function (message, type = "info") {

        const container = document.getElementById("toast-container");
        if (!container) return;

        const toast = document.createElement("div");
        toast.className = "toast " + type;
        toast.innerText = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateX(40px)";
        }, 2800);

        setTimeout(() => {
            toast.remove();
        }, 3200);
    };


    /* ---------- DARK MODE ---------- */

    const btn = document.getElementById("theme-toggle");

    if (!btn) return;


    const saved = localStorage.getItem("theme");

    if (saved === "dark") {
        document.body.classList.add("dark");
        btn.innerText = "☀️";
    }


    btn.addEventListener("click", () => {

        document.body.classList.toggle("dark");
        document.documentElement.classList.toggle("manual-theme");

        if (document.body.classList.contains("dark")) {

            localStorage.setItem("theme", "dark");
            btn.innerText = "☀️";

        } else {

            localStorage.setItem("theme", "light");
            btn.innerText = "🌙";
            document.documentElement.classList.remove("manual-theme");
        }

    });

});
// ===============================
// 3D CARDS PARALLAX
// ===============================

document.querySelectorAll(".card").forEach(card => {

    let bounds;

    function rotateToMouse(e) {

        const mouseX = e.clientX;
        const mouseY = e.clientY;

        const leftX = mouseX - bounds.x;
        const topY = mouseY - bounds.y;

        const center = {
            x: leftX - bounds.width / 2,
            y: topY - bounds.height / 2
        };

        const distance = Math.sqrt(center.x ** 2 + center.y ** 2);

        card.style.transform = `
            perspective(900px)
            rotateX(${center.y / 12}deg)
            rotateY(${-center.x / 12}deg)
            scale3d(1.04,1.04,1.04)
        `;
    }

    card.addEventListener("mouseenter", () => {
        bounds = card.getBoundingClientRect();
        card.style.transition = "transform .1s";
    });

    card.addEventListener("mousemove", rotateToMouse);

    card.addEventListener("mouseleave", () => {
        card.style.transition = "transform .5s ease";
        card.style.transform = "none";
    });

});
// ===============================
// LIVE SEARCH
// ===============================

const searchInput = document.getElementById("liveSearch");

if (searchInput) {

    searchInput.addEventListener("input", async () => {

        const q = searchInput.value.trim();

        const res = await fetch("api/search_materials.php?q=" + q);

        const html = await res.text();

        document.getElementById("tableBody").innerHTML = html;

    });

}
