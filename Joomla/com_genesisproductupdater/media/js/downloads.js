document.addEventListener('click', function (event) {
    const opener = event.target.closest('[data-pu-dialog]');
    if (opener) {
        const dialog = document.getElementById(opener.dataset.puDialog);
        if (dialog) dialog.showModal();
        return;
    }
    const close = event.target.closest('.pu-dialog-close');
    if (close) close.closest('dialog').close();
    if (event.target.matches('.pu-changelog-dialog')) event.target.close();
});
