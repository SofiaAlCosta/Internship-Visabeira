document.addEventListener("DOMContentLoaded", () => {
    const formularios = document.querySelectorAll("form");

    formularios.forEach(form => {
        form.addEventListener("submit", function (e) {
            const inputs = form.querySelectorAll("input[required]");
            let todosPreenchidos = true;

            inputs.forEach(input => {
                if (input.value.trim() === "") {
                    todosPreenchidos = false;
                    input.style.border = "2px solid red";
                } else {
                    input.style.border = "1px solid #ccc";
                }
            });

            if (!todosPreenchidos) {
                e.preventDefault();
                alert("Por favor, preenche todos os campos obrigatórios.");
            }
        });
    });
});