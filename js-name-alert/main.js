function myFunction() {
    const nome = document.getElementById("name").value.trim();

    if (nome === "") {
        alert("Deve inserir o seu nome");
    } else {
        document.body.innerHTML = `
            <div>
                <h1>Olá, ${nome}</h1>
                <button onclick="location.reload()">Voltar</button>
            </div>
        `;
    }
}