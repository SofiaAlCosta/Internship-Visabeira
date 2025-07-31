# taka-airways-system

Projeto desenvolvido no âmbito do meu estágio na Visabeira, com o objetivo de criar um sistema de reservas de voos para uma companhia fictícia: **Taka Airways**.

## 🧠 Objetivo

Desenvolver uma aplicação web funcional onde os utilizadores podem:

- Pesquisar voos disponíveis
- Consultar detalhes de cada voo
- Efetuar reservas de bilhetes
- Gerir o seu perfil e histórico de reservas

O projeto também inclui um **painel de administração**, permitindo a gestão completa de:

- Voos
- Utilizadores
- Reservas
- Exportação de dados para PDF

Este sistema permitiu-me consolidar conhecimentos em **PHP**, **MySQL**, **autenticação de sessões**, e lógica CRUD em contexto multi-utilizador (cliente e admin).

## ⚙️ Tecnologias utilizadas

- PHP
- HTML5
- CSS3
- JavaScript
- MySQL
- Bootstrap
- Font Awesome
- Dompdf (para exportar reservas em PDF)

## 📸 Demonstração

### Página Inicial
![Homepage](./assets/images/readme/homepage.png)

### Autenticação
- **Registo**  
  ![Registo](./assets/images/readme/registo.png)
- **Login**  
  ![Login](./assets/images/readme/login.png)

### Cliente

- **Visualizar Voos Disponíveis**  
  ![Voos](./assets/images/readme/voos.png)

- **Minhas Reservas**  
  ![Reservas](./assets/images/readme/reserva.png)

- **Editar Perfil**  
  ![Perfil](./assets/images/readme/perfil.png)

### Administrador

- **Dashboard**  
  ![Dashboard](./assets/images/readme/dashboard.png)

- **Gestão de Utilizadores**  
  ![Utilizadores](./assets/images/readme/gerir_utilizadores.png)

- **Gestão de Voos**  
  ![Voos Admin](./assets/images/readme/gerir_voos.png)

- **Gestão de Reservas**  
  ![Reservas Admin](./assets/images/readme/gerir_reservas.png)

---

## 🚀 Como executar

1. Clonar o repositório:

   ```bash
   git clone https://github.com/SofiaAlCosta/Internship-Visabeira
   ```

2. Aceder à pasta do projeto:

   ```bash
   cd taka-airways-system
   ```

3. Colocar o projeto num servidor com suporte PHP (ex: XAMPP, WAMP ou Laragon).
4. Aceder via navegador a:

   ```bash
   http://localhost:8080/taka-airways-system/index.php
   ```
