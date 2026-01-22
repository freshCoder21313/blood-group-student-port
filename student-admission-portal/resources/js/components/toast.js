export default (message, type = 'success') => {
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { message, type }
    }));
};
