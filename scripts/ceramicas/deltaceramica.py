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


def coletar_pedidos_delta():
    url_login = "https://portal-api.deltaceramica.com.br/api/v1/session"
    url_pedidos = "https://portal-api.deltaceramica.com.br/api/v1/orders_for_collect"

    payload = {
        "weu_email": "logistica@modestoemussato.com.br",
        "password": "Marinapisos",
        "weu_id": 910
    }

    # Realiza login e captura o token
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

    # Requisição de pedidos com token
    headers = {
        "Authorization": f"Bearer {token}",
        "Accept": "application/json"
    }

    pedidos_res = requests.get(url_pedidos, headers=headers)
    if pedidos_res.status_code != 200:
        print(f"❌ Erro ao buscar pedidos: {pedidos_res.status_code}")
        return []

    pedidos_data = pedidos_res.json().get("orders", [])


    pedidos = []

    for pedido_data in pedidos_data:
        for item in pedido_data.get("itens", []):
            pedido = {
                "representante": item.get("rep_nome", ""),
                "data_pedido": item.get("itped_data_entrega", "")[:10],
                "cliente": pedido_data.get("cli_nome", ""),
                "numero_pedido": item.get("cod_pedido", ""),
                "codigo_produto": item.get("cod_produto", ""),
                "descricao_produto": item.get("dsc_abreviado", ""),
                "industria": "Delta Cerâmica",
                "qtd_pallets": int(float(item.get("qtd_pallet", 0))),
                "tipo_produto": "CERAMICA/PISO",
                "total_m2": float(item.get("qtd_saldo", 0)),
                "peso_total": float(item.get("peso_bru", 0)),
                "cidade": pedido_data.get("cli_cidade", "").split("/")[0],
                "estado": pedido_data.get("cli_cidade", "/").split("/")[-1],
            }
        pedidos.append(pedido)


    print(f"📦 Total de pedidos coletados: {len(pedidos)}")
    return pedidos
