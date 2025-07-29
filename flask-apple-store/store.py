from flask import Flask, render_template
import pymysql.cursors

app = Flask(__name__)

@app.route("/")
def home():
    return render_template("home.html", categoria_data=select_from_database("SELECT * FROM categorias"))





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