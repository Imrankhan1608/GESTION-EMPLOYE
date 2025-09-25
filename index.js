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
    document.getElementById(motif).classList.remove("hidden");
  }
}

// pour confirmer //
function adver() {
  document.getElementById('add').style.display='none';
  document.getElementById('adver').style.display='block';
}

// pour les sections //
document.querySelectorAll('.select-options li').forEach(item => {
  item.addEventListener('click', function() {
    const select = document.querySelector('#service');
    const selectedDiv = document.querySelector('.select-selected');
    
    // Met à jour le texte affiché
    selectedDiv.textContent = this.textContent;
    
    // Met à jour la valeur réelle du select
    select.value = this.getAttribute('data-value');