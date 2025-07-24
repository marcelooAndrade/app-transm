from ceramicas import cedasa, cecafi, novaporcelanato, incopisos, deltaceramica, villagres, almeida
from envio.logistica_api import enviar_para_api_logistica
from datetime import datetime
import json
import traceback

# Lista de funções de coleta
coletores = [
    cedasa.coletar_pedidos_cedasa,
    cecafi.coletar_pedidos_cecafi,
    novaporcelanato.coletar_pedidos_nova_porcelanato,
    incopisos.coletar_pedidos_incopisos,
    deltaceramica.coletar_pedidos_delta,
    villagres.coletar_pedidos_villagres,
    almeida.coletar_pedidos_almeida
    # adicionar outros aqui
]

pedidos = []

print("📦 Iniciando coletas de pedidos...")
for coletor in coletores:
    try:
        resultado = coletor()
        pedidos.extend(resultado)
        print(f"✅ {coletor.__name__} retornou {len(resultado)} pedidos.")
    except Exception as e:
        print(f"❌ Erro ao coletar com {coletor.__name__}: {e}")

print(f"📊 Total de pedidos coletados: {len(pedidos)}")

# Conversão segura de datas
for pedido in pedidos:
    try:
        print(f"⚠️ Dados do pedido: {pedido}")

        data_raw = pedido.get("data_pedido", "")
        if isinstance(data_raw, str) and "/" in data_raw:
            pedido["data_pedido"] = datetime.strptime(data_raw, "%d/%m/%Y").date().isoformat()
    except Exception as e:
        print(f"⚠️ Erro ao converter data '{pedido.get('data_pedido')}': {e}")
        pedido["data_pedido"] = None  # fallback seguro

print("⏱️ Scraping finalizado em:", datetime.now().strftime("%Y-%m-%d %H:%M:%S"))

# Envia para a API
try:
    res = enviar_para_api_logistica(pedidos)
    print(f"📨 Enviado para API com status: {res.status_code}")
    # print(f"🔁 Resposta: {res.json()}")
except Exception as e:
    print("❌ Erro ao enviar para API:")
    traceback.print_exc()
    if 'res' in locals():
        print("🪵 Corpo da resposta:", res.text)
