function getInputLen(span, input, inf_boundary, sup_boundary) {
    let compteur = document.getElementById(span);
    let inputLen = document.getElementById(input).value.length + 1;

    if (inputLen == 1) {
        compteur.textContent = "1 caractère";
    } else {
        compteur.textContent = inputLen + " caractères";
    }

    if (inputLen < inf_boundary || inputLen > sup_boundary) {
        // En dehors des min max :
        // -> Reset des classes de coloration
        compteur.classList.remove("bg-secondary");
        compteur.classList.remove("bg-success");
        compteur.classList.remove("bg-danger");

        // -> Ajout de la classe de coloration rouge
        compteur.classList.add("bg-danger");
    } else if (inputLen >= inf_boundary && inputLen <= sup_boundary) {
        // Dans l'interval min max inclus :
        // -> Reset des classes de coloration
        compteur.classList.remove("bg-secondary");
        compteur.classList.remove("bg-success");
        compteur.classList.remove("bg-danger");

        // -> Ajout de la classe de coloration verte
        compteur.classList.add("bg-success");
    }
}
