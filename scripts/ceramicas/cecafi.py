from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from bs4 import BeautifulSoup
from datetime import datetime
from utils.webdriver import configurar_navegador  # Agora usando o navegador configurado
from datetime import datetime
from selenium.webdriver.chrome.options import Options



def coletar_pedidos_cecafi():
    options = Options()
    options.add_argument('--headless')
    options.add_argument('--disable-gpu')
    options.add_argument('--no-sandbox')

    driver = configurar_navegador(headless=True)  # ou False para ver o navegador

    driver.get("https://transportadora.ccfonline.com.br/agendamento")

    WebDriverWait(driver, 20).until(EC.presence_of_element_located((By.NAME, "cnpj")))
    driver.find_element(By.NAME, "cnpj").send_keys("47.528.089/0001-27")
    driver.find_element(By.NAME, "password").send_keys("47528089")
    driver.find_element(By.XPATH, '//button[contains(text(),"Login")]').click()

    # Fecha a modal de política se ela existir
    try:
        WebDriverWait(driver, 5).until(
            EC.element_to_be_clickable((By.XPATH, '//button[contains(text(),"Prosseguir")]'))
        ).click()
    except:
        pass

    # Clica no botão Gerar Planilha
    WebDriverWait(driver, 15).until(
        EC.element_to_be_clickable((By.XPATH, '//button[contains(text(),"Gerar Planilha")]'))
    ).click()

    # Aguarda a tabela carregar
    WebDriverWait(driver, 20).until(
        EC.presence_of_element_located((By.ID, "tabela_result"))
    )

    soup = BeautifulSoup(driver.page_source, "html.parser")
    driver.quit()

    pedidos = []
    tabela = soup.find("table", {"id": "tabela_result"})

    if not tabela:
        return []

    linhas = tabela.find_all("tr")[1:]  # Ignora o cabeçalho

    for linha in linhas:
        dados = [td.get_text(strip=True) for td in linha.find_all("td")]
        if not dados or len(dados) < 15:
            continue

        pedido = {
            "representante": dados[13],
            "data_pedido": datetime.today().date().isoformat(),
            "cliente": dados[1],
            "numero_pedido": dados[6],
            "codigo_produto": dados[8],
            "descricao_produto": dados[9],
            "industria": "CECAFI",
            "qtd_pallets": int(float(dados[11])),
            "tipo_produto": "CERAMICA/PISO",
            "total_m2": float(dados[10]),
            "peso_total": float(dados[12]),
            "cidade": dados[3],
            "estado": dados[4],
        }

        pedidos.append(pedido)
    return pedidos
