document.addEventListener('click', function (event) {
    const opener = event.target.closest('[data-genesis-up-dialog]');
    if (opener) {
        const dialog = document.getElementById(opener.dataset.puDialog);
        if (dialog) dialog.showModal();
        return;
    }
    const close = event.target.closest('.genesis-up-dialog-close');
    if (close) close.closest('dialog').close();
    if (event.target.matches('.genesis-up-changelog-dialog')) event.target.close();
});
