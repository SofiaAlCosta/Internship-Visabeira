from flask import Flask, render_template
import pymysql.cursors
import sys

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

if __name__ == "__main__":
    app.run(debug=True, port=5000)