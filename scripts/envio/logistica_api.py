import requests
import json

def enviar_para_api_logistica(pedidos):
    url = "http://localhost:8000/api/pedidos/scraping"

    payload = {
        "pedidos": pedidos
    }
    print("📤 Payload sendo enviado")
    #print(json.dumps(payload, indent=2, ensure_ascii=False))

    try:
        response = requests.post(url, json=payload)
        response.raise_for_status()
        return response
    except requests.exceptions.HTTPError as e:
        print("❌ Erro HTTP:", e)
        print("🪵 Resposta:", response.text)
        raise
    except Exception as e:
        print("❌ Erro geral:", str(e))
        raise
