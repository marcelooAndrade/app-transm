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

os.environ["XDG_CACHE_HOME"] = "/tmp/selenium_cache"

def coletar_pedidos_nova_porcelanato():
    print("🔒 Acessando Nova Porcelanato com Selenium...")

    options = Options()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")

    driver = webdriver.Chrome(options=options)
    driver.get("http://portal.novaporcelanato.com.br/pedidos/pedidos")

    WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.NAME, "form_user_id")))
    driver.find_element(By.NAME, "form_user_id").send_keys("47528089000127")
    driver.find_element(By.NAME, "form_password").send_keys("transm.2019")
    driver.find_element(By.NAME, "sistema").send_keys("TRP")
    driver.find_element(By.NAME, "submit").click()
    print("➡️ Formulário de login enviado")

    try:
        WebDriverWait(driver, 10).until(EC.frame_to_be_available_and_switch_to_it((By.NAME, "topFrame")))
        print("✅ Frame topFrame acessado com sucesso!")

        monta_carga_btn = WebDriverWait(driver, 10).until(
            EC.element_to_be_clickable((By.XPATH, "//input[@value='Monta Carga']"))
        )
        monta_carga_btn.click()
        print("🚚 Botão 'Monta Carga' clicado com sucesso!")

        # Etapa 1: entra no frame 'detalhe'
        driver.switch_to.default_content()
        WebDriverWait(driver, 10).until(EC.frame_to_be_available_and_switch_to_it((By.NAME, "detalhe")))
        print("✅ Frame detalhe acessado com sucesso!")

        # Etapa 2: entra no iframe 'topo_reserva'
        WebDriverWait(driver, 10).until(EC.frame_to_be_available_and_switch_to_it((By.ID, "topo_reserva")))
        print("✅ Iframe topo_reserva acessado com sucesso!")

        # Aguarda o conteúdo carregar
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "conteudo_reserva")))
        print("✅ Conteúdo da reserva carregado!")

        html = driver.page_source

        soup = BeautifulSoup(html, "html.parser")
        tabela = soup.find("table", class_="reservafixo")
        if not tabela:
            print("❌ Tabela com classe 'reservafixo' não encontrada.")
            return

        pedidos = []
        linhas = tabela.find_all("tr")[2:]

        for linha in linhas:
            colunas = linha.find_all("td")
            dados = [col.text.strip().replace(".", "").replace(",", ".") for col in colunas]

            if len(dados) < 14:
                continue

            try:

                cidade_uf = dados[4].strip().split(" / ")
                cidade = cidade_uf[0].strip()
                estado = cidade_uf[1].strip() if len(cidade_uf) > 1 else ""

                pedido = {
                    "representante": "Não tem",
                    "data_pedido": dados[1],
                    "cliente": dados[3],
                    "numero_pedido": dados[0],
                    "codigo_produto": "",
                    "descricao_produto": dados[5],
                    "industria": "Nova Porcelanato",
                    "qtd_pallets": dados[15],         # <-- Pallets, ex: '4,00'
                    "tipo_produto": "CERAMICA/PISO",
                    "total_m2": dados[8],             # <-- Qtdade (m²), ex: '653,76'
                    "peso_total": dados[14],          # <-- Peso Bruto, ex: '4.831,89'
                    "cidade": cidade,
                    "estado": estado,
                }

                pedidos.append(pedido)
            except Exception as e:
                print(f"❌ Erro ao converter linha: {e}")
                continue
        return pedidos

    except Exception as e:
        print(f"❌ Erro inesperado: {e}")

    finally:
        driver.quit()
