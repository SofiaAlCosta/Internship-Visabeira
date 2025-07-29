from flask import Flask, render_template, redirect, request, session, flash
import pymysql.cursors
import sys
import hashlib
from datetime import datetime, timedelta
import uuid

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
    except Exception:
        morada = None
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

if __name__ == "__main__":
    app.run(debug=True, port=5000)