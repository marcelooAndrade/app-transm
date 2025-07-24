# envio/formatador.py

def formatar_pedido(dados, industria):
    return {
        "representante": dados.get("representante", "").strip(),
        "data_pedido": dados.get("data_pedido", "").strip(),
        "cliente": dados.get("cliente", "").strip(),
        "numero_pedido": dados.get("numero_pedido", "").strip(),
        "codigo_produto": dados.get("codigo_produto", "").strip(),
        "descricao_produto": dados.get("descricao_produto", "").strip(),
        "industria": industria,
        "qtd_pallets": dados.get("qtd_pallets", 0),
        "tipo_produto": dados.get("tipo_produto", "").strip(),
        "total_m2": dados.get("total_m2", 0),
        "peso_total": dados.get("peso_total", 0),
        "cidade": dados.get("cidade", "").strip(),
        "estado": dados.get("estado", "").strip(),
    }
