import os

appdata = os.environ.get('APPDATA', '')
cfg_path = os.path.join(appdata, 'RustDesk', 'config', 'RustDesk.toml')

if os.path.exists(cfg_path):
    with open(cfg_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Prepend or append rendezvous_server and relay_server and key
    additions = """rendezvous_server = '10.4.4.40:21116'
relay_server = '10.4.4.40:21117'
key = 'wB87lzDNRVD2atA73W41bVP0VmYuzl0oiWU2QitF0hU='
"""
    if "rendezvous_server" not in content:
        new_content = additions + "\n" + content
        with open(cfg_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print("Updated RustDesk.toml with local server 10.4.4.40")
    else:
        print("Already configured")
else:
    print("RustDesk.toml not found")
