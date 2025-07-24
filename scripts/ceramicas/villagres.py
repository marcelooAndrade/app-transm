import os

# Corrige erro de permissão no cache do Selenium
os.environ["XDG_CACHE_HOME"] = "/tmp/selenium_cache"
os.makedirs(os.environ["XDG_CACHE_HOME"], exist_ok=True)

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from bs4 import BeautifulSoup
import os
import time

from datetime import datetime
import requests


def coletar_pedidos_villagres():
    url_login = "https://api-portal.villagres.com.br/api/v1/session"
    url_pedidos = "https://api-portal.villagres.com.br/api/v1/orders_for_collect"

    payload = {
        "weu_email": "logistica@modestoemussato.com.br",
        "password": "Marina@110967",
        "weu_id": 327
    }

    login_res = requests.post(url_login, json=payload)

    if login_res.status_code != 200:
        print(f"❌ Falha no login Delta: {login_res.status_code}")
        return []

    dados = login_res.json()
    token = dados.get("auth_token")
    if not token:
        print("❌ Token não encontrado, não é possível prosseguir.")
        return []

    print("🔑 Token JWT obtido com sucesso!")

    headers = {
        "Authorization": f"Bearer {token}",
        "Accept": "application/json"
    }

    pedidos_res = requests.get(url_pedidos, headers=headers)
    if pedidos_res.status_code != 200:
        print(f"❌ Erro ao buscar pedidos: {pedidos_res.status_code}")
        return []

    pedidos_data = pedidos_res.json().get("orders", [])
    if not pedidos_data:
        print("⚠️ Nenhum pedido retornado.")
        return []

    for key in pedidos_data[0]:
        print(f" - {key}: {pedidos_data[0][key]}")

    pedidos = []

    for pedido_data in pedidos_data:
        cidade_estado = pedido_data.get("cli_cidade", "").split("/")
        cidade = cidade_estado[0] if len(cidade_estado) > 0 else "ND"
        estado = cidade_estado[1] if len(cidade_estado) > 1 else "ND"

        for item in pedido_data.get("itens", []):
            try:
                data_entrega = item.get("itped_data_entrega", "")
                data_formatada = datetime.strptime(data_entrega[:10], '%Y-%m-%d').date() if data_entrega else None
            except Exception as e:
                print(f"⚠️ Data inválida: '{data_entrega}' - {e}")
                data_formatada = None

            pedidos.append({
                "representante": item.get("rep_nome", ""),
                "data_pedido": data_formatada,
                "cliente": pedido_data.get("cli_nome", ""),
                "numero_pedido": item.get("cod_pedido", ""),
                "codigo_produto": item.get("cod_produto", ""),
                "descricao_produto": item.get("dsc_abreviado", ""),
                "industria": "Villagres Cerâmica",
                "qtd_pallets": int(float(item.get("qtd_pallet", 0) or 0)),
                "tipo_produto": "CERAMICA/PISO",
                "total_m2": float(item.get("qtd_saldo", 0) or 0),
                "peso_total": float(item.get("peso_bru", 0) or 0),
                "cidade": cidade,
                "estado": estado,
            })

    print(f"📦 Total de pedidos coletados: {len(pedidos)}")
    return pedidos
