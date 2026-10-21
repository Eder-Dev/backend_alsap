(() => {
    const buttons = document.querySelectorAll('[data-state]');
    const status = document.querySelector('.export-status');
    let exporting = false;

    function setStatus(message, type = '') {
        status.textContent = message;
        status.className = `export-status ${type}`.trim();
    }

    function filenameFromResponse(response) {
        const disposition = response.headers.get('Content-Disposition') || '';
        const match = disposition.match(/filename="?([^";]+)"?/i);
        return match ? match[1] : 'acervo_completo.xls';
    }

    async function exportAcervo(button) {
        if (exporting) return;

        exporting = true;
        buttons.forEach((item) => {
            item.disabled = true;
            item.classList.toggle('is-loading', item === button);
        });
        setStatus('Carregando dados...');

        try {
            const state = button.dataset.state || '';
            const params = new URLSearchParams({ export: '1' });
            if (state) params.set('estado', state);

            const response = await fetch(`gráficos.php?${params.toString()}`, {
                headers: { Accept: 'application/vnd.ms-excel, application/json' },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('export-failed');
            }

            setStatus('Preparando arquivo...');
            const file = await response.blob();
            const downloadUrl = URL.createObjectURL(file);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = filenameFromResponse(response);
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(downloadUrl);
            setStatus('Download iniciado!', 'success');
        } catch (error) {
            setStatus('Não foi possível carregar os dados do acervo. Tente novamente em alguns instantes.', 'error');
        } finally {
            exporting = false;
            buttons.forEach((item) => {
                item.disabled = false;
                item.classList.remove('is-loading');
            });
        }
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => exportAcervo(button));
    });
})();
