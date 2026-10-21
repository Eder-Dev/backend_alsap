const menuItems = document.querySelectorAll('.nav-list li a');
const contentSection = document.getElementById('content');

const sections = {
    "Dashboard": "<div class='text'>Bem-vindo ao Painel de Controle!</div>",
    "Pesquisadores": "<div class='text'>Lista de Pesquisadores</div>",
    "Acervo": "<div class='text'>Aqui está o acervo</div>",
    "Gráficos": "<div class='text'>Visualize os gráficos</div>",
    "Configurações": "<div class='text'>Altere as configurações</div>",
};

menuItems.forEach(item => {
    item.addEventListener('click', (event) => {
        event.preventDefault();
        const sectionName = item.querySelector('.links_name').textContent;
        console.log(`Menu item clicked: ${sectionName}`);
        location.href = `${sectionName.toLowerCase()}.php`;
    });
});

let sidebar = document.querySelector(".sidebar");
let closeBtn = document.querySelector("#btn");
let searchBtn = document.querySelector(".bx-search");

closeBtn.addEventListener("click", () => {
    sidebar.classList.toggle("open");
    menuBtnChange();
})

searchBtn.addEventListener("click", () => {
    sidebar.classList.toggle("open");
    menuBtnChange();
})

function menuBtnChange() {
    if (sidebar.classList.contains("open")) {
        closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
    } else {
        closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
    }
}

menuBtnChange()