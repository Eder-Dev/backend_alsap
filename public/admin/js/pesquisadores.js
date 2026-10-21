(() => {
    const apiUrl = '../api/pesquisadoresControllers.php';
    const modal = document.getElementById('researcherModal');
    const form = document.getElementById('researcherForm');
    const openButton = document.getElementById('createDocumentBtn');
    const closeButton = document.getElementById('researcherModalClose');
    const cancelButton = document.getElementById('researcherCancel');
    const submitButton = document.getElementById('researcherSubmit');
    const message = document.getElementById('researcherFormMessage');
    const grid = document.getElementById('researchersGrid');
    const count = document.getElementById('researchersCount');

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, (character) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
        }[character]));
    }

    function photoPath(value) {
        const photo = String(value || '').trim();
        return photo.startsWith('assets/') ? `../${photo}` : photo;
    }

    function renderResearchers(researchers) {
        count.textContent = researchers.length ? `Total de pesquisadores: ${researchers.length}` : '';
        if (!researchers.length) {
            grid.className = 'empty-state';
            grid.innerHTML = '<p>Nenhum pesquisador encontrado.</p>';
            return;
        }

        grid.className = 'cards-grid';
        grid.innerHTML = researchers.map((researcher) => {
            const name = escapeHtml(researcher.nome || 'Pesquisador');
            const institution = escapeHtml(researcher.instituicao || 'Não informada');
            const role = escapeHtml(researcher.cargo || 'Pesquisador');
            const lattes = escapeHtml(researcher.lattes || '');
            const photo = photoPath(researcher.foto);
            const photoMarkup = photo
                ? `<img src="${escapeHtml(photo)}" alt="Foto de ${name}" class="profile-photo">`
                : '<div class="profile-photo-placeholder">Sem foto</div>';
            const linkMarkup = lattes
                ? `<a href="${lattes}" target="_blank" rel="noopener" class="lattes-link">Ver Lattes</a>`
                : '';

            return `<article class="pesquisador-card">
                ${photoMarkup}
                <div class="card-body">
                    <h3>${name}</h3>
                    <p class="card-meta"><strong>Instituição:</strong> ${institution}</p>
                    <p class="card-meta"><strong>Cargo:</strong> ${role}</p>
                    ${linkMarkup}
                </div>
            </article>`;
        }).join('');
    }

    async function reloadResearchers() {
        const response = await fetch(apiUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!response.ok) throw new Error('list-failed');
        const researchers = await response.json();
        if (!Array.isArray(researchers)) throw new Error('invalid-list');
        renderResearchers(researchers);
    }

    reloadResearchers().catch(() => {
        count.textContent = '';
        grid.className = 'empty-state';
        grid.innerHTML = '<p>Não foi possível carregar os pesquisadores. Tente novamente.</p>';
    });

    function showMessage(text, type = 'error') {
        message.textContent = text;
        message.className = `form-message visible ${type}`;
    }

    function clearMessage() {
        message.textContent = '';
        message.className = 'form-message';
    }

    function openModal() {
        clearMessage();
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        document.getElementById('researcherName').focus();
    }

    function closeModal() {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        form.reset();
        clearMessage();
    }

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearMessage();
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        submitButton.disabled = true;
        cancelButton.disabled = true;
        submitButton.textContent = 'Cadastrando...';

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify(Object.fromEntries(new FormData(form)))
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Não foi possível cadastrar o pesquisador.');
            }

            await reloadResearchers();
            closeModal();
        } catch (error) {
            showMessage(error.message || 'Não foi possível cadastrar o pesquisador. Tente novamente.');
        } finally {
            submitButton.disabled = false;
            cancelButton.disabled = false;
            submitButton.textContent = 'Cadastrar pesquisador';
        }
    });
})();
