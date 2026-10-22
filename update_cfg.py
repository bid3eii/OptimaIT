import os

appdata = os.environ.get('APPDATA', '')
cfg_path = os.path.join(appdata, 'RustDesk', 'config', 'RustDesk2.toml')
os.makedirs(os.path.dirname(cfg_path), exist_ok=True)

content = """key = 'wB87lzDNRVD2atA73W41bVP0VmYuzl0oiWU2QitF0hU='
rendezvous_server = '10.4.4.40:21116'
relay_server = '10.4.4.40:21117'
api_server = ''
nat_type = 1

[options]
approve-mode = 'click'
verification-method = 'both'
"""

with open(cfg_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated:", cfg_path)
