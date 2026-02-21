export function startLoading(id, button) {
    document.getElementById('spinner').style.display = 'block';
    if (document.getElementById(id)) document.getElementById(id).style.opacity = '.2';
    if (document.getElementById(button)) document.getElementById(button).setAttribute('disabled', true);
}

export function endLoading(id, button) {
    setTimeout(function () {
        document.getElementById('spinner').style.display = 'none';
        if (document.getElementById(id)) document.getElementById(id).style.opacity = '1';
        if (document.getElementById(button)) document.getElementById(button).removeAttribute('disabled');
    }, 300)
}
