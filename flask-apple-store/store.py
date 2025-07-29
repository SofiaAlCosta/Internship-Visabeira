from flask import Flask, render_template, redirect, request, session, flash
import pymysql.cursors
import sys
import hashlib

app = Flask(__name__)

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