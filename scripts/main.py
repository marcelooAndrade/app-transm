from ceramicas import cedasa, cecafi, novaporcelanato, incopisos, deltaceramica, villagres, almeida # e os outros módulos conforme necessário
from envio.logistica_api import enviar_para_api_logistica
from datetime import datetime
import json
import traceback

import datetime

# Lista de funções de coleta
coletores = [
    cedasa.coletar_pedidos_cedasa,
    cecafi.coletar_pedidos_cecafi,
    novaporcelanato.coletar_pedidos_nova_porcelanato,
    incopisos.coletar_pedidos_incopisos,
    deltaceramica.coletar_pedidos_delta,
    villagres.coletar_pedidos_villagres,
    almeida.coletar_pedidos_almeida
    # adicionar os outros: coletar_pedidos_xyz
]

pedidos = []

# Executa todas as coletas e junta os pedidos
for coletor in coletores:
    try:
        resultado = coletor()
        pedidos.extend(resultado)
    except Exception as e:
        print(f"❌ Erro ao coletar com {coletor.__name__}: {e}")

# Converte as datas
for pedido in pedidos:
    try:
        if "/" in pedido["data_pedido"]:
            pedido["data_pedido"] = datetime.strptime(pedido["data_pedido"], "%d/%m/%Y").date().isoformat()
    except Exception as e:
        print(f"❌ Erro ao converter data '{pedido['data_pedido']}': {e}")


# Exibe os dados coletados
#print("📦 Dados coletados:")
#print(json.dumps(pedidos, indent=2, ensure_ascii=False))
print("Scraping iniciado")
print("Horário:", datetime.datetime.now())

# Envia para a API com tratamento de erro detalhado
try:
    res = enviar_para_api_logistica(pedidos)
    print(f"📨 Status: {res.status_code}")
    #print(f"🔁 Resposta: {res.json()}")
except Exception as e:
    print("❌ Erro ao enviar para API:")
    traceback.print_exc()
    if 'res' in locals():
        print("🪵 Corpo da resposta:", res.text)
