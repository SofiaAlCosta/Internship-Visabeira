from flask import Flask, render_template, redirect, request, session, flash, jsonify
import pymysql.cursors
import sys
import hashlib
from datetime import datetime, timedelta
import uuid
import os
from werkzeug.utils import secure_filename

app = Flask(__name__)
app.secret_key = "minha_chave_super_secreta_123"

@app.route("/")
def home():
    return render_template("home.html", categoria_data=select_from_database("SELECT * FROM categorias"))

@app.route("/product/<int:id>")
def product(id):
    product_data = select_from_database(f"SELECT * FROM produto WHERE ProdutoID = {id}")
    if not product_data:
        return "<p>Product not found</p>"
    produto = product_data[0]
    print("Produto:", produto, flush=True)
    print('This is error output', file=sys.stderr)

    imagens = [img.strip() for img in produto["Imagens"].split(",")] if "Imagens" in produto else []
    print("IMAGENS CARREGADAS:", imagens, flush=True)
    cores = [
        {"cor_hex": "#c4ab97"},
        {"cor_hex": "#c4c0b5"},
        {"cor_hex": "#f2f1ed"},
        {"cor_hex": "#505050"}
    ]

    reviews = select_from_database(f"""
        SELECT r.Comentario, r.Avaliacao, c.Nome
        FROM reviews r
        JOIN clientes c ON r.ClienteID = c.ClienteID
        WHERE r.ProdutoID = {id}
    """)

    return render_template("product.html", product={
        "Nome": produto["Nome"],
        "Preco": produto["Preco"],
        "VideoURL": produto["VideoURL"],
        "ProdutoID": produto["ProdutoID"],
        "Imagens": imagens,
        "Cores": cores
    }, categoria_data=select_from_database("SELECT * FROM categorias"), reviews=reviews)

@app.route("/register", methods=["GET", "POST"])
def register():
    if app.logger:
        app.logger.debug("Rota de registo acionada")

    if request.method == "POST":
        nome = request.form.get("nome")
        email = request.form.get("email")
        telefone = request.form.get("telefone")
        data_nascimento = request.form.get("data_nascimento")
        genero = request.form.get("genero")
        raw_password = request.form.get("password")
        password = hash_password(raw_password)

        try:
            connection = pymysql.connect(
                host='127.0.0.1',
                port=3306,
                user='root',
                password='',
                database='loja_online',
                cursorclass=pymysql.cursors.DictCursor
            )
            with connection.cursor() as cursor:
                cursor.execute(
                    """
                    INSERT INTO clientes (Nome, Email, Telefone, Data_Nascimento, Genero, Password)
                    VALUES (%s, %s, %s, %s, %s, %s)
                    """,
                    (nome, email, telefone, data_nascimento, genero, password)
                )
                connection.commit()
            connection.close()
            return render_template("login.html", success=True, categoria_data=select_from_database("SELECT * FROM categorias"))
        except Exception as e:
            return f"<p>Erro ao registar: {e}</p>"
    return render_template("register.html", categoria_data=select_from_database("SELECT * FROM categorias"))

@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        email = request.form.get("email")
        raw_password = request.form.get("password")
        hashed_password = hash_password(raw_password)

        try:
            connection = pymysql.connect(
                host='127.0.0.1',
                port=3306,
                user='root',
                password='',
                database='loja_online',
                cursorclass=pymysql.cursors.DictCursor
            )
            with connection.cursor() as cursor:
                cursor.execute(
                    "SELECT * FROM clientes WHERE Email = %s AND Password = %s",
                    (email, hashed_password)
                )
                user = cursor.fetchone()

            connection.close()

            if user:
                session['nome'] = user['Nome']
                session['email'] = user['Email']
                session['telefone'] = user.get('Telefone', '')
                session['data_nascimento'] = user.get('Data_Nascimento', '')
                session['genero'] = user.get('Genero', '')
                session['clienteid'] = user.get('ClienteID', '')
                return redirect("/")
            else:
                flash("Credenciais inválidas. Tente novamente.")
                return redirect("/login")

        except Exception as e:
            return f"<p>Erro ao tentar fazer login: {e}</p>"
    return render_template("login.html", categoria_data=select_from_database("SELECT * FROM categorias"))

@app.route("/recoverpassword", methods=["GET", "POST"])
def recover_password():
    if request.method == "POST":
        email = request.form.get("email")
        cliente = select_from_database("SELECT ClienteID FROM clientes WHERE Email = %s", [email])
        if not cliente:
            return "<p>Email não encontrado.</p>"

        cliente_id = cliente[0]["ClienteID"]
        token = str(uuid.uuid4())
        expira = datetime.now() + timedelta(hours=1)

        try:
            connection = pymysql.connect(
                host='127.0.0.1', user='root', password='', database='loja_online',
                cursorclass=pymysql.cursors.DictCursor
            )
            with connection.cursor() as cursor:
                cursor.execute("""
                    INSERT INTO tokens_recuperacao (ClienteID, Token, ExpiraEm)
                    VALUES (%s, %s, %s)
                """, (cliente_id, token, expira))
                connection.commit()
            connection.close()

            # Simula envio de email
            link = f"http://127.0.0.1:5000/reset_password/{token}"
            return f"<p>Link de recuperação (simulado): <a href='{link}'>{link}</a></p>"

        except Exception as e:
            return f"<p>Erro ao gerar token: {e}</p>"

    return render_template("recoverpassword.html", categoria_data=select_from_database("SELECT * FROM categorias"))

@app.route("/profile", methods=["GET", "POST"])
def profile():
    cliente_email = session.get('email')
    if not cliente_email:
        return redirect("/login")

    cliente_id = session.get("clienteid")
    encomendas = []

    if request.method == "POST":
        rua = request.form.get("rua")
        codigo_postal = request.form.get("codigo_postal")
        cidade = request.form.get("cidade")
        pais = request.form.get("pais")
        novo_email = request.form.get("email")
        novo_telefone = request.form.get("telefone")
        nova_password = request.form.get("password")

        try:
            connection = pymysql.connect(
                host='127.0.0.1',
                port=3306,
                user='root',
                password='',
                database='loja_online',
                cursorclass=pymysql.cursors.DictCursor
            )
            with connection.cursor() as cursor:
                if nova_password:
                    hashed = hash_password(nova_password)
                    cursor.execute("""
                        UPDATE clientes
                        SET Email = %s, Telefone = %s, Password = %s
                        WHERE ClienteID = %s
                    """, (novo_email, novo_telefone, hashed, cliente_id))
                else:
                    cursor.execute("""
                        UPDATE clientes
                        SET Email = %s, Telefone = %s
                        WHERE ClienteID = %s
                    """, (novo_email, novo_telefone, cliente_id))

                cursor.execute("SELECT MoradaID FROM clientes WHERE ClienteID = %s", (cliente_id,))
                morada_info = cursor.fetchone()
                morada_id = morada_info["MoradaID"] if morada_info else None

                if rua and cidade and codigo_postal and pais:
                    if morada_id:
                        cursor.execute("""
                            UPDATE moradas
                            SET Rua = %s, Cidade = %s, Codigo_postal = %s, Pais = %s
                            WHERE MoradaID = %s
                        """, (rua, cidade, codigo_postal, pais, morada_id))
                    else:
                        cursor.execute("""
                            INSERT INTO moradas (Rua, Cidade, Codigo_postal, Pais)
                            VALUES (%s, %s, %s, %s)
                        """, (rua, cidade, codigo_postal, pais))
                        nova_morada_id = cursor.lastrowid
                        cursor.execute("""
                            UPDATE clientes SET MoradaID = %s WHERE ClienteID = %s
                        """, (nova_morada_id, cliente_id))

                connection.commit()

                session["email"] = novo_email
                session["telefone"] = novo_telefone

        except Exception as e:
            return f"<p>Erro ao atualizar perfil: {e}</p>"
        finally:
            connection.close()

        return redirect("/profile")

    morada = None
    encomendas = []
    try:
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor
        )
        with connection.cursor() as cursor:
            cursor.execute("""
                SELECT m.*
                FROM clientes c
                LEFT JOIN moradas m ON c.MoradaID = m.MoradaID
                WHERE c.ClienteID = %s
            """, (cliente_id,))
            morada = cursor.fetchone()

            cursor.execute("""
                SELECT e.EncomendaID, e.Data, e.Total
                FROM encomendas e
                WHERE e.ClienteID = %s
                ORDER BY e.Data DESC
            """, (cliente_id,))
            encomendas = cursor.fetchall()

            for encomenda in encomendas:
                cursor.execute("""
                    SELECT ep.Quantidade, p.Nome, p.Preco
                    FROM encomendas_produtos ep
                    JOIN produto p ON ep.ProdutoID = p.ProdutoID
                    WHERE ep.EncomendaID = %s
                """, (encomenda["EncomendaID"],))
                encomenda["produtos"] = cursor.fetchall()

    except Exception as e:
        print("Erro ao carregar morada/encomendas:", e)
    finally:
        connection.close()

    return render_template("profile.html", categoria_data=select_from_database("SELECT * FROM categorias"), encomendas=encomendas, morada=morada)

@app.route("/logout")
def logout():
    session.clear()
    return redirect("/")

@app.route("/apagar_conta", methods=["POST"])
def apagar_conta():
    cliente_email = session.get("email")
    if not cliente_email:
        return redirect("/login")
    try:
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor
        )
        with connection.cursor() as cursor:
            cursor.execute("SELECT ClienteID FROM clientes WHERE Email = %s", (cliente_email,))
            cliente = cursor.fetchone()
            if cliente:
                cliente_id = cliente["ClienteID"]
                cursor.execute("DELETE FROM moradas WHERE ClienteID = %s", (cliente_id,))
                cursor.execute("DELETE FROM encomendas WHERE ClienteID = %s", (cliente_id,))
                cursor.execute("DELETE FROM carrinhos WHERE ClienteID = %s", (cliente_id,))
                cursor.execute("DELETE FROM clientes WHERE ClienteID = %s", (cliente_id,))
                connection.commit()
        connection.close()
        session.clear()
        return redirect("/")
    except Exception as e:
        return f"<p>Erro ao apagar conta: {e}</p>"

@app.route("/product/<int:id>/review", methods=["POST"])
def insertreview(id):
    comentario = request.form.get("comentario")
    cliente = session.get("clienteid")
    if not cliente:
        return redirect("/login")
    avaliacao = request.form.get('avaliacao')
    
    try:
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor
        )
        with connection.cursor() as cursor:
            cursor.execute(
                """
                INSERT INTO `loja_online`.`reviews`
                    (`ProdutoID`,
                    `ClienteID`,
                    `Comentario`,
                    `Avaliacao`)
                    VALUES
                    (%s,%s,%s,%s);
                """, (id, cliente, comentario, avaliacao)
            )
            connection.commit()
        connection.close()
        return redirect(f"/product/{id}")
    except Exception as e:
        return f"Erro"

@app.route("/favoritos")
def favoritos():
    cliente_id = session.get("clienteid")
    if not cliente_id:
        return redirect("/login")

    query = """
        SELECT p.*
        FROM favoritos f
        JOIN produto p ON f.ProdutoID = p.ProdutoID
        WHERE f.ClienteID = %s
    """
    produtos_favoritos = select_from_database(query, [cliente_id])

    return render_template("wishlist.html",
        produtos=produtos_favoritos,
        categoria_data=select_from_database("SELECT * FROM categorias")
    )

@app.route("/toggle_favorito/<int:produto_id>", methods=["POST"])
def toggle_favorito(produto_id):
    cliente_id = session.get("clienteid")
    if not cliente_id:
        return redirect("/login")

    connection = pymysql.connect(
        host='127.0.0.1',
        user='root',
        password='',
        database='loja_online',
        cursorclass=pymysql.cursors.DictCursor
    )
    with connection.cursor() as cursor:
        cursor.execute("""
            SELECT * FROM favoritos WHERE ClienteID = %s AND ProdutoID = %s
        """, (cliente_id, produto_id))
        existe = cursor.fetchone()

        if existe:
            cursor.execute("""
                DELETE FROM favoritos WHERE ClienteID = %s AND ProdutoID = %s
            """, (cliente_id, produto_id))
        else:
            cursor.execute("""
                INSERT INTO favoritos (ClienteID, ProdutoID) VALUES (%s, %s)
            """, (cliente_id, produto_id))

        connection.commit()
    connection.close()
    return redirect(f"/product/{produto_id}")

@app.route("/remover_favorito/<int:produto_id>", methods=["POST"])
def remover_favorito(produto_id):
    cliente_id = session.get("clienteid")
    if not cliente_id:
        return redirect("/login")

    query = "DELETE FROM favoritos WHERE ClienteID = %s AND ProdutoID = %s"
    execute_query(query, (cliente_id, produto_id))

    return redirect("/favoritos")

@app.route("/pesquisa")
def pesquisa():
    termo = request.args.get("q", "").strip()
    ordenar = request.args.get("ordenar", "")
    preco_max = request.args.get("preco_max", "")
    categoria_id = request.args.get("categoria_id", "")

    if not termo:
        return render_template(
            "search.html",
            produtos=[],
            termo="",
            ordenar=ordenar,
            preco_max=preco_max,
            categoria_id=categoria_id,
            categorias=select_from_database("SELECT * FROM categorias"),
            categoria_data=select_from_database("SELECT * FROM categorias")
        )

    query = """
        SELECT p.*
        FROM produto p
        LEFT JOIN categoria_produto cp ON p.ProdutoID = cp.ProdutoID
        WHERE LOWER(p.Nome) LIKE %s
    """
    params = [f"%{termo.lower()}%"]

    if categoria_id:
        query += " AND cp.CategoriaID = %s"
        params.append(categoria_id)

    if preco_max:
        query += " AND p.Preco <= %s"
        params.append(preco_max)

    if ordenar == "preco_asc":
        query += " ORDER BY p.Preco ASC"
    elif ordenar == "preco_desc":
        query += " ORDER BY p.Preco DESC"
    elif ordenar == "novos":
        query += " ORDER BY p.ProdutoID DESC"

    resultados = select_from_database(query, params)

    return render_template(
        "search.html",
        produtos=resultados,
        termo=termo,
        ordenar=ordenar,
        preco_max=preco_max,
        categoria_id=int(categoria_id) if categoria_id else "",
        categorias=select_from_database("SELECT * FROM categorias"),
        categoria_data=select_from_database("SELECT * FROM categorias")
    )

@app.route("/autocomplete")
def autocomplete():
    termo = request.args.get("q", "").lower()
    resultados = select_from_database("""
        SELECT ProdutoID, Nome FROM produto WHERE LOWER(Nome) LIKE %s LIMIT 5
    """, [f"%{termo}%"])
    return jsonify(resultados)

@app.route("/cart")
def cart():
    cliente_id = session.get("clienteid")
    if not cliente_id:
        return redirect("/login")

    try:
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor
        )

        with connection.cursor() as cursor:
            cursor.execute("SELECT CarrinhoID FROM carrinhos WHERE ClienteID = %s", (cliente_id,))
            carrinho = cursor.fetchone()

            if not carrinho:
                return render_template("cart.html", produtos=[], total=0, categoria_data=select_from_database("SELECT * FROM categorias"))

            carrinho_id = carrinho["CarrinhoID"]

            cursor.execute("""
                SELECT p.Nome, p.Preco, p.Capa, p.ProdutoID, cp.Quantidade
                FROM carrinho_produtos cp
                JOIN produto p ON cp.ProdutoID = p.ProdutoID
                WHERE cp.CarrinhoID = %s
            """, (carrinho_id,))
            produtos = cursor.fetchall()

        connection.close()

        for p in produtos:
            p["subtotal"] = p["Preco"] * p["Quantidade"]

        total = sum(p["subtotal"] for p in produtos)

        return render_template("cart.html", produtos=produtos, total=total, categoria_data=select_from_database("SELECT * FROM categorias"))

    except Exception as e:
        return f"<p>Erro ao carregar carrinho: {e}</p>"

@app.route("/adicionar_ao_carrinho/<int:produto_id>", methods=["POST"])
def adicionar_ao_carrinho(produto_id):
    cliente_id = session.get("clienteid")
    if not cliente_id:
        return redirect("/login")

    try:
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor
        )

        with connection.cursor() as cursor:
            cursor.execute("SELECT CarrinhoID FROM carrinhos WHERE ClienteID = %s", (cliente_id,))
            carrinho = cursor.fetchone()

            if not carrinho:
                cursor.execute("INSERT INTO carrinhos (ClienteID, Data) VALUES (%s, NOW())", (cliente_id,))
                connection.commit()
                carrinho_id = cursor.lastrowid
            else:
                carrinho_id = carrinho["CarrinhoID"]

            cursor.execute("""
                SELECT Quantidade FROM carrinho_produtos
                WHERE CarrinhoID = %s AND ProdutoID = %s
            """, (carrinho_id, produto_id))
            existente = cursor.fetchone()

            if existente:
                nova_qtd = existente["Quantidade"] + 1
                cursor.execute("""
                    UPDATE carrinho_produtos
                    SET Quantidade = %s
                    WHERE CarrinhoID = %s AND ProdutoID = %s
                """, (nova_qtd, carrinho_id, produto_id))
            else:
                cursor.execute("""
                    INSERT INTO carrinho_produtos (CarrinhoID, ProdutoID, Quantidade)
                    VALUES (%s, %s, 1)
                """, (carrinho_id, produto_id))
                # Remover o produto da wishlist (favoritos)
                cursor.execute("""
                    DELETE FROM favoritos WHERE ClienteID = %s AND ProdutoID = %s
                """, (cliente_id, produto_id))

            connection.commit()
        connection.close()
        return redirect("/cart")

    except Exception as e:
        return f"<p>Erro ao adicionar ao carrinho: {e}</p>"
    
@app.route("/remover_do_carrinho/<int:produto_id>", methods=["POST"])
def remover_do_carrinho(produto_id):
    cliente_id = session.get("clienteid")
    if not cliente_id:
        return redirect("/login")

    try:
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor
        )

        with connection.cursor() as cursor:
            cursor.execute("SELECT CarrinhoID FROM carrinhos WHERE ClienteID = %s", (cliente_id,))
            carrinho = cursor.fetchone()

            if not carrinho:
                return redirect("/cart")

            carrinho_id = carrinho["CarrinhoID"]

            cursor.execute("""
                DELETE FROM carrinho_produtos
                WHERE CarrinhoID = %s AND ProdutoID = %s
            """, (carrinho_id, produto_id))
            connection.commit()

        connection.close()
        return redirect("/cart")

    except Exception as e:
        return f"<p>Erro ao remover produto do carrinho: {e}</p>"

@app.route("/atualizar_quantidade/<int:produto_id>", methods=["POST"])
def atualizar_quantidade(produto_id):
    cliente_id = session.get("clienteid")
    if not cliente_id:
        return redirect("/login")

    operacao = request.form.get("operacao") 

    try:
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor
        )

        with connection.cursor() as cursor:
            cursor.execute("SELECT CarrinhoID FROM carrinhos WHERE ClienteID = %s", (cliente_id,))
            carrinho = cursor.fetchone()
            if not carrinho:
                return redirect("/cart")

            carrinho_id = carrinho["CarrinhoID"]

            cursor.execute("""
                SELECT Quantidade FROM carrinho_produtos
                WHERE CarrinhoID = %s AND ProdutoID = %s
            """, (carrinho_id, produto_id))
            existente = cursor.fetchone()

            if not existente:
                return redirect("/cart")

            qtd_atual = existente["Quantidade"]

            if operacao == "incrementar":
                nova_qtd = qtd_atual + 1
            elif operacao == "decrementar":
                nova_qtd = qtd_atual - 1
            else:
                return redirect("/cart")

            if nova_qtd <= 0:
                cursor.execute("""
                    DELETE FROM carrinho_produtos
                    WHERE CarrinhoID = %s AND ProdutoID = %s
                """, (carrinho_id, produto_id))
            else:
                cursor.execute("""
                    UPDATE carrinho_produtos
                    SET Quantidade = %s
                    WHERE CarrinhoID = %s AND ProdutoID = %s
                """, (nova_qtd, carrinho_id, produto_id))

            connection.commit()

        connection.close()
        return redirect("/cart")

    except Exception as e:
        return f"<p>Erro ao atualizar quantidade: {e}</p>"

@app.route("/checkout", methods=["POST"])
def checkout():
    cliente_id = session.get("clienteid")
    if not cliente_id:
        return redirect("/login")

    try:
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor
        )

        with connection.cursor() as cursor:
            cursor.execute("SELECT CarrinhoID FROM carrinhos WHERE ClienteID = %s", (cliente_id,))
            carrinho = cursor.fetchone()
            if not carrinho:
                return "<p>O seu carrinho está vazio.</p>"

            carrinho_id = carrinho["CarrinhoID"]

            cursor.execute("""
                SELECT p.ProdutoID, p.Nome, p.Preco, cp.Quantidade, p.Capa
                FROM carrinho_produtos cp
                JOIN produto p ON cp.ProdutoID = p.ProdutoID
                WHERE cp.CarrinhoID = %s
            """, (carrinho_id,))
            produtos = cursor.fetchall()
            if not produtos:
                return "<p>O seu carrinho está vazio.</p>"

            cursor.execute("""
                SELECT m.*
                FROM moradas m
                JOIN clientes c ON c.MoradaID = m.MoradaID
                WHERE c.ClienteID = %s
            """, (cliente_id,))
            morada = cursor.fetchone()
            if not morada:
                return "<p>Precisa de adicionar uma morada antes de finalizar a compra.</p>"

        connection.close()

        total = sum(p["Preco"] * p["Quantidade"] for p in produtos)

        return render_template("checkout.html", produtos=produtos, total=total, morada=morada, categoria_data=select_from_database("SELECT * FROM categorias"))

    except Exception as e:
        return f"<p>Erro no checkout: {e}</p>"

@app.route("/confirmar_encomenda", methods=["POST"])
def confirmar_encomenda():
    cliente_id = session.get("clienteid")
    if not cliente_id:
        return redirect("/login")

    try:
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor
        )

        with connection.cursor() as cursor:
            cursor.execute("SELECT CarrinhoID FROM carrinhos WHERE ClienteID = %s", (cliente_id,))
            carrinho = cursor.fetchone()
            if not carrinho:
                return "<p>Carrinho vazio.</p>"

            carrinho_id = carrinho["CarrinhoID"]

            cursor.execute("""
                SELECT ProdutoID, Quantidade
                FROM carrinho_produtos
                WHERE CarrinhoID = %s
            """, (carrinho_id,))
            produtos = cursor.fetchall()
            if not produtos:
                return "<p>Carrinho sem produtos.</p>"

            cursor.execute("""
                SELECT SUM(p.Preco * cp.Quantidade) AS total
                FROM carrinho_produtos cp
                JOIN produto p ON cp.ProdutoID = p.ProdutoID
                WHERE cp.CarrinhoID = %s
            """, (carrinho_id,))
            total = cursor.fetchone()["total"]

            metodo_pagamento = request.form.get("pagamento")

            cursor.execute("""
                INSERT INTO encomendas (ClienteID, Data, Total, MetodoPagamento)
                VALUES (%s, NOW(), %s, %s)
            """, (cliente_id, total, metodo_pagamento))
            encomenda_id = cursor.lastrowid

            for p in produtos:
                cursor.execute("""
                    INSERT INTO encomendas_produtos (EncomendaID, ProdutoID, Quantidade)
                    VALUES (%s, %s, %s)
                """, (encomenda_id, p["ProdutoID"], p["Quantidade"]))

            cursor.execute("DELETE FROM carrinho_produtos WHERE CarrinhoID = %s", (carrinho_id,))
            cursor.execute("DELETE FROM carrinhos WHERE CarrinhoID = %s", (carrinho_id,))

            connection.commit()
        connection.close()

        return redirect("/encomenda_confirmada")

    except Exception as e:
        return f"<p>Erro ao confirmar encomenda: {e}</p>"
    
@app.route("/encomenda_confirmada")
def encomenda_confirmada():
    return render_template("confirmation.html", categoria_data=select_from_database("SELECT * FROM categorias"))






def select_from_database(select_query, params=None):
    rt = []
    try:
        print("A tentar conectar...")
        connection = pymysql.connect(
            host='127.0.0.1',
            port=3306,
            user='root',
            password='',
            database='loja_online',
            cursorclass=pymysql.cursors.DictCursor,
            connect_timeout=5
        )
        print("Ligado com sucesso!")
        with connection.cursor() as cursor:
            cursor.execute(select_query, params or ())
            for row in cursor:
                rt.append(row)
        connection.close()
    except Exception as e:
        print("Erro:", e)
    return rt

@app.context_processor
def utility_functions():
    def get_produtos_por_categoria(categoria_id):
        query = """
            SELECT p.ProdutoID, p.Nome 
            FROM produto p
            JOIN categoria_produto cp ON p.ProdutoID = cp.ProdutoID
            WHERE cp.CategoriaID = %s
        """
        return select_from_database(query, [categoria_id])
    return dict(get_produtos_por_categoria=get_produtos_por_categoria)

def hash_password(password):
    return hashlib.sha256(password.encode("utf-8")).hexdigest()

def execute_query(query, params=None):
    connection = pymysql.connect(
        host='127.0.0.1',
        user='root',
        password='',
        database='loja_online',
        cursorclass=pymysql.cursors.DictCursor
    )
    try:
        with connection.cursor() as cursor:
            cursor.execute(query, params)
            connection.commit()
    finally:
        connection.close()

if __name__ == "__main__":
    app.run(debug=True, port=5000)