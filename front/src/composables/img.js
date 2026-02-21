import Swal from "sweetalert2";


export function handleImg(event, width, height, data, key) {
    return new Promise((resolve, reject) => {
        data[key] = null;
        data[`${key}_alert`] = false;
        data[`${key}Url`] = null;

        const input = event.target;
        if (input.files && input.files[0]) {
            const file = input.files[0];

            // Verifique se o arquivo é uma imagem
            if (!file.type.startsWith('image/')) {
                // Limpe o conteúdo do input se o arquivo não for uma imagem
                input.value = '';
                Swal.fire({
                        title: 'Arquivo Inválido!',
                        text: "O arquivo selecionado não é uma imagem.",
                        icon: 'error',
                        confirmButtonColor: "#34c38f",
                        confirmButtonText: "Ok",
                    })
                    .then((result) => {
                        return !!result.value;

                    })
                    .catch(() => {
                        return false;
                    });
                return;
            }

            const reader = new FileReader();

            reader.onload = (e) => {
                data[key] = e.target.result;

                const image = new Image();
                image.onload = () => {
                    // Verifique se as dimensões da imagem são menores do que as permitidas
                    data[`${key}_alert`] = image.width < width || image.height < height;

                    resolve(data);
                };

                image.onerror = reject;
                // Defina o src após configurar o onload e onerror
                image.src = e.target.result;
            };

            reader.onerror = reject;
            reader.readAsDataURL(input.files[0]);
        } else {
            reject("Nenhum arquivo selecionado");
        }
    });
}

/**
 * Função que salva o arquivo vindo do Cropper e sua url
 * @param blob vindo do Cropper
 * @param data
 * @param key do objeto a ser atualizado
 * @return {data} Retorna valor recebido via parâmetro atualizado
 */
export function setImageBlob(blob, data, key) {
    data[`${key}Url`] = URL.createObjectURL(blob);
    data[key] = new File([blob], "cropped-image.png", {type: 'image/png'});

    return data;
}


/**
 * Função de reset de valores da imagem
 * @param id do input
 * @param data
 * @param key do objeto a ser atualizado
 * @return {data} Retorna valor recebido via parâmetro atualizado
 */
export function resetImageBlob(id, data, key) {
    if (document.getElementById(id)) document.getElementById(id).value = '';
    data[key] = null;
    // data[`${key}Url`] = null;

    return data;
}

