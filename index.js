// Afficher la section correspondant au motif
function afficherSection() {
    const sections = [
        "mission",
        "repos_normal",
        "conge_annuel",
        "permission_remunere",
        "permission_nonremunere",
        "formation",
        "autre"
    ];

    const motif = document.getElementById("motif").value;

    sections.forEach(section => {
        document.getElementById(section).classList.add("hidden");
    });

    if (motif) {
        const selectedSection = document.getElementById(motif);
        if (selectedSection) selectedSection.classList.remove("hidden");
    }
}

// Afficher le bloc de confirmation
function adver() {
    document.getElementById('add').style.display = 'none';
    document.getElementById('adver').style.display = 'block';
}

// Gestion custom select (si tu utilises des <li> pour remplacer le select)
document.querySelectorAll('.select-options li').forEach(item => {
    item.addEventListener('click', function() {
        const select = document.querySelector('#service');
        const selectedDiv = document.querySelector('.select-selected');

        // Met à jour le texte affiché
        if (selectedDiv) selectedDiv.textContent = this.textContent;

        // Met à jour la valeur réelle du select
        if (select) select.value = this.getAttribute('data-value');
    });
});

// Calcul automatique du nombre de jours
const dateDebut = document.getElementById('date_debut');
const dateFin = document.getElementById('date_fin');
const nombreJours = document.getElementById('nombre_jours');

function calculerJours() {
    if (dateDebut.value && dateFin.value) {
        const start = new Date(dateDebut.value);
        const end = new Date(dateFin.value);
        const diffTime = end - start;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
        nombreJours.value = diffDays > 0 ? diffDays : 0;
    }
}

if (dateDebut && dateFin && nombreJours) {
    dateDebut.addEventListener('change', calculerJours);
    dateFin.addEventListener('change', calculerJours);
}
