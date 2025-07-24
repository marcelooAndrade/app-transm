# ceramica_almeida.py
import os

# Corrige erro de permissão no cache do Selenium
os.environ["XDG_CACHE_HOME"] = "/tmp/selenium_cache"
os.makedirs(os.environ["XDG_CACHE_HOME"], exist_ok=True)

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from bs4 import BeautifulSoup
import time

from datetime import datetime

def coletar_pedidos_almeida():
    options = Options()
    options.add_argument("--headless")  # descomente para rodar sem abrir o navegador
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")

    driver = webdriver.Chrome(options=options)
    wait = WebDriverWait(driver, 20)

    # 1) Acessa a página de login
    driver.get("http://clientes.ceramicaalmeida.com.br:51110/Login/Transportadora")

    # Preenche o e-mail
    email = wait.until(EC.presence_of_element_located((By.ID, "Email")))
    email.clear()
    email.send_keys("faturamento@modestoemussato.com.br")

    # Clica em Entrar para ir à tela de senha
    driver.find_element(By.XPATH, "//input[@type='submit' and @value='Entrar']").click()

    # 2) Aguarda o campo de senha aparecer
    senha = wait.until(EC.presence_of_element_located((By.ID, "Senha")))
    senha.clear()
    senha.send_keys("modesto@123")
    driver.find_element(By.XPATH, "//input[@type='submit' and @value='Entrar']").click()

    # 3) Aguarda o redirecionamento para a Home
    wait.until(EC.url_contains("/Transportadora/Home"))
    print("✅ Login Cerâmica Almeida realizado com sucesso!")

    # 4) Aguarda a tabela de pedidos aparecer
    wait.until(EC.presence_of_element_located((By.ID, "tabelaultimospedidos")))

    # Dá um tempo extra caso a tabela seja preenchida dinamicamente
    time.sleep(3)

    html = driver.page_source
    driver.quit()

    # 5) Lê a tabela via BeautifulSoup
    soup = BeautifulSoup(html, "html.parser")
    tabela = soup.find("table", id="tabelaultimospedidos")
    if not tabela:
        print("❌ Tabela 'tabelaultimospedidos' não encontrada.")
        return []

    pedidos = []
    linhas = tabela.find_all("tr")[1:]  # pula o cabeçalho

    for linha in linhas:
        col = linha.find_all("td")
        if not col:
            continue
        # Exemplo, ajuste índices conforme a sua tabela
        pedidos.append({
                "representante": "Não tem",
                "data_pedido": None,
                "cliente": col[1].get_text(strip=True),
                "numero_pedido": col[0].get_text(strip=True),
                "codigo_produto": "Não tem",
                "descricao_produto": "Não tem",
                "industria": "Almeida",
                "qtd_pallets": 0,
                "tipo_produto": "CERAMICA/PISO",
                "total_m2": 0.00,
                "peso_total": float(col[2].get_text(strip=True).replace(",", ".")),
                "cidade": "ND",
                "estado": "ND",
        })

    print(f"📦 Total de pedidos Almeida: {len(pedidos)}")
    return pedidos
