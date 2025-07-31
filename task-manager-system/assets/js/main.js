document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll("form");

  forms.forEach(form => {
    form.addEventListener("submit", e => {
      const requiredInputs = form.querySelectorAll("input[required], textarea[required]");
      let valid = true;

      requiredInputs.forEach(input => {
        if (input.value.trim() === "") {
          valid = false;
          input.style.border = "2px solid red";
        } else {
          input.style.border = "1px solid #ccc";
        }
      });

      if (!valid) {
        e.preventDefault();
        alert("Por favor preenche todos os campos obrigatórios.");
      }
    });
  });
});