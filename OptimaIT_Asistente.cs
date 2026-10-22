using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Net;
using System.Security.Principal;
using System.Text;
using System.Threading;
using System.Windows.Forms;

namespace OptimaIT
{
    public class AssistantForm : Form
    {
        private string sessionId = "";
        private string rustdeskId = "";
        private string clientType = "personal"; // "personal" o "empresa"
        private string serverUrl = "http://10.4.4.40/OptimaIT";
        private System.Windows.Forms.Timer timer;
        private Label lblStatus;
        private Label lblId;
        private Label lblBadgeType;
        private Button btnDisconnect;

        public AssistantForm(string sId, string cType)
        {
            this.sessionId = sId;
            this.clientType = cType;
            InitUI();
            this.Load += AssistantForm_Load;
            this.FormClosing += AssistantForm_FormClosing;
        }

        public static bool IsAdministrator()
        {
            try
            {
                WindowsIdentity identity = WindowsIdentity.GetCurrent();
                WindowsPrincipal principal = new WindowsPrincipal(identity);
                return principal.IsInRole(WindowsBuiltInRole.Administrator);
            }
            catch
            {
                return false;
            }
        }

        public static bool RelaunchAsAdmin(string[] args)
        {
            try
            {
                ProcessStartInfo proc = new ProcessStartInfo();
                proc.UseShellExecute = true;
                proc.WorkingDirectory = AppDomain.CurrentDomain.BaseDirectory;
                proc.FileName = Application.ExecutablePath;
                string newArgs = "--elevated";
                if (args != null && args.Length > 0)
                {
                    newArgs += " " + string.Join(" ", args);
                }
                proc.Arguments = newArgs;
                proc.Verb = "runas"; // Requests Windows UAC elevation
                Process p = Process.Start(proc);
                if (p != null)
                {
                    Environment.Exit(0);
                    return true;
                }
            }
            catch
            {
                // User rejected UAC prompt or elevation is unavailable; return false to allow non-admin fallback!
            }
            return false;
        }

        private void InitUI()
        {
            this.Text = "OptimaIT - Soporte Técnico Remoto";
            this.Size = new Size(420, 390);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.ShowInTaskbar = true;
            this.BackColor = Color.FromArgb(248, 249, 250);

            try
            {
                string iconPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, @"res\icon.ico");
                if (File.Exists(iconPath))
                {
                    this.Icon = new Icon(iconPath);
                }
            }
            catch { }

            // Header Banner
            Panel pnlHeader = new Panel();
            pnlHeader.Dock = DockStyle.Top;
            pnlHeader.Height = 85;
            pnlHeader.BackColor = Color.FromArgb(56, 116, 204); // OptimaIT Blue

            Label lblTitle = new Label();
            lblTitle.Text = "OptimaIT Soporte Remoto";
            lblTitle.Font = new Font("Segoe UI", 15, FontStyle.Bold);
            lblTitle.ForeColor = Color.White;
            lblTitle.Location = new Point(20, 16);
            lblTitle.AutoSize = true;
            pnlHeader.Controls.Add(lblTitle);

            Label lblSub = new Label();
            lblSub.Text = "Sesión: " + (string.IsNullOrEmpty(sessionId) ? "Directa" : sessionId);
            lblSub.Font = new Font("Segoe UI", 10, FontStyle.Regular);
            lblSub.ForeColor = Color.FromArgb(230, 240, 255);
            lblSub.Location = new Point(22, 48);
            lblSub.AutoSize = true;
            pnlHeader.Controls.Add(lblSub);

            lblBadgeType = new Label();
            lblBadgeType.Text = clientType == "empresa" ? "🏢 PERFIL EMPRESARIAL" : "👤 ASISTENCIA RÁPIDA";
            lblBadgeType.Font = new Font("Segoe UI", 8, FontStyle.Bold);
            lblBadgeType.ForeColor = Color.White;
            lblBadgeType.BackColor = clientType == "empresa" ? Color.FromArgb(231, 114, 34) : Color.FromArgb(40, 80, 140);
            lblBadgeType.Padding = new Padding(6, 3, 6, 3);
            lblBadgeType.Location = new Point(230, 48);
            lblBadgeType.AutoSize = true;
            pnlHeader.Controls.Add(lblBadgeType);

            this.Controls.Add(pnlHeader);

            // Body
            lblStatus = new Label();
            lblStatus.Text = "Iniciando servicio de asistencia...";
            lblStatus.Font = new Font("Segoe UI", 11, FontStyle.Bold);
            lblStatus.ForeColor = Color.FromArgb(40, 50, 70);
            lblStatus.Location = new Point(25, 110);
            lblStatus.Size = new Size(360, 30);
            lblStatus.TextAlign = ContentAlignment.MiddleCenter;
            this.Controls.Add(lblStatus);

            lblId = new Label();
            lblId.Text = "Obteniendo ID...";
            lblId.Font = new Font("Consolas", 14, FontStyle.Bold);
            lblId.ForeColor = Color.FromArgb(231, 114, 34); // OptimaIT Orange
            lblId.Location = new Point(25, 145);
            lblId.Size = new Size(360, 35);
            lblId.TextAlign = ContentAlignment.MiddleCenter;
            this.Controls.Add(lblId);

            Label lblDesc = new Label();
            if (clientType == "empresa")
            {
                lblDesc.Text = "Equipo configurado para soporte desatendido con permisos de administrador.\nIniciará automáticamente con el sistema.";
            }
            else
            {
                lblDesc.Text = "El técnico se conectará a su equipo a través de la red local.\nNo cierre esta ventana mientras recibe soporte.";
            }
            lblDesc.Font = new Font("Segoe UI", 9, FontStyle.Regular);
            lblDesc.ForeColor = Color.FromArgb(100, 110, 125);
            lblDesc.Location = new Point(25, 190);
            lblDesc.Size = new Size(360, 50);
            lblDesc.TextAlign = ContentAlignment.MiddleCenter;
            this.Controls.Add(lblDesc);

            // Disconnect button
            btnDisconnect = new Button();
            btnDisconnect.Text = "Finalizar Soporte";
            btnDisconnect.Font = new Font("Segoe UI", 10, FontStyle.Bold);
            btnDisconnect.BackColor = Color.FromArgb(239, 68, 68);
            btnDisconnect.ForeColor = Color.White;
            btnDisconnect.FlatStyle = FlatStyle.Flat;
            btnDisconnect.FlatAppearance.BorderSize = 0;
            btnDisconnect.Size = new Size(180, 40);
            btnDisconnect.Location = new Point(115, 270);
            btnDisconnect.Cursor = Cursors.Hand;
            btnDisconnect.Click += (s, e) => { this.Close(); };
            this.Controls.Add(btnDisconnect);
        }

        private void AssistantForm_Load(object sender, EventArgs e)
        {
            Thread t = new Thread(BackgroundConnect);
            t.IsBackground = true;
            t.Start();
        }

        private void BackgroundConnect()
        {
            try
            {
                string baseDir = AppDomain.CurrentDomain.BaseDirectory;
                string currentExePath = Application.ExecutablePath;
                string localApp = Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData);
                // Server config parameters
                string serverIp = "10.4.4.40";
                string serverKey = "wB87lzDNRVD2atA73W41bVP0VmYuzl0oiWU2QitF0hU=";
                string encodedEngineName = "OptimaIT-host=" + serverIp + ",key=" + serverKey + ".exe";

                // Target engine paths
                string progFilesRust = @"C:\Program Files\RustDesk\rustdesk.exe";
                string localAppRust = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), @"rustdesk\rustdesk.exe");
                string tempDir = Path.GetTempPath();
                string encodedInstallerPath = Path.Combine(tempDir, encodedEngineName);

                // Ensure the installer archive is downloaded
                if (!File.Exists(encodedInstallerPath))
                {
                    this.Invoke(new Action(() => { lblStatus.Text = "Descargando motor de conexión..."; }));
                    try
                    {
                        using (WebClient wcDown = new WebClient())
                        {
                            wcDown.DownloadFile(serverUrl + "/" + encodedEngineName, encodedInstallerPath);
                        }
                    }
                    catch
                    {
                        try
                        {
                            using (WebClient wcDown = new WebClient())
                            {
                                wcDown.DownloadFile(serverUrl + "/OptimaIT.exe", encodedInstallerPath);
                            }
                        }
                        catch { }
                    }
                }

                // If real rustdesk.exe does not exist yet, extract it silently from the SFX
                if (!File.Exists(progFilesRust) && !File.Exists(localAppRust) && File.Exists(encodedInstallerPath))
                {
                    try
                    {
                        ProcessStartInfo psiExt = new ProcessStartInfo();
                        psiExt.FileName = encodedInstallerPath;
                        psiExt.Arguments = "-y";
                        psiExt.UseShellExecute = false;
                        psiExt.CreateNoWindow = true;
                        psiExt.WindowStyle = ProcessWindowStyle.Hidden;
                        using (Process pExt = Process.Start(psiExt))
                        {
                            if (pExt != null) pExt.WaitForExit(8000);
                        }
                    }
                    catch { }
                }

                // Pick the REAL binary (not the SFX launcher) so CLI flags work
                string engineExe = "";
                if (File.Exists(progFilesRust))
                {
                    engineExe = progFilesRust;
                }
                else if (File.Exists(localAppRust))
                {
                    engineExe = localAppRust;
                }
                else
                {
                    engineExe = encodedInstallerPath;
                }

                // Configuration TOML content
                string newConfig = "rendezvous_server = '" + serverIp + ":21116'\n" +
                                   "relay_server = '" + serverIp + ":21117'\n" +
                                   "key = '" + serverKey + "'\n" +
                                   "serial = 999999999\n" +
                                   "\n[options]\n" +
                                   "key = '" + serverKey + "'\n" +
                                   "custom-rendezvous-server = '" + serverIp + ":21116'\n" +
                                   "rendezvous-servers = '" + serverIp + ":21116'\n" +
                                   "relay-server = '" + serverIp + ":21117'\n" +
                                   "approve-mode = 'both'\n" +
                                   "verification-method = 'both'\n";

                // Save config to user, system and service profiles
                try
                {
                    string appData = Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData);
                    string cfgDir = Path.Combine(appData, @"RustDesk\config");
                    Directory.CreateDirectory(cfgDir);
                    File.WriteAllText(Path.Combine(cfgDir, "RustDesk2.toml"), newConfig);
                    File.WriteAllText(Path.Combine(cfgDir, "RustDesk.toml"), newConfig);

                    string progData = Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData);
                    string progCfg = Path.Combine(progData, @"RustDesk\config");
                    Directory.CreateDirectory(progCfg);
                    File.WriteAllText(Path.Combine(progCfg, "RustDesk2.toml"), newConfig);
                    File.WriteAllText(Path.Combine(progCfg, "RustDesk.toml"), newConfig);

                    // Try LocalService config directory if accessible
                    string localServiceCfg = @"C:\Windows\ServiceProfiles\LocalService\AppData\Roaming\RustDesk\config";
                    if (Directory.Exists(Path.GetDirectoryName(localServiceCfg)))
                    {
                        Directory.CreateDirectory(localServiceCfg);
                        File.WriteAllText(Path.Combine(localServiceCfg, "RustDesk2.toml"), newConfig);
                        File.WriteAllText(Path.Combine(localServiceCfg, "RustDesk.toml"), newConfig);
                    }
                }
                catch { }

                bool wasInstalled = false;
                if (clientType == "empresa")
                {
                    this.Invoke(new Action(() => { lblStatus.Text = "Configurando servicio desatendido permanente..."; }));
                    try
                    {
                        // Stop old service if running
                        try
                        {
                            ProcessStartInfo psiStop = new ProcessStartInfo("sc.exe", "stop RustDesk");
                            psiStop.UseShellExecute = false;
                            psiStop.CreateNoWindow = true;
                            using (Process pStop = Process.Start(psiStop)) { if (pStop != null) pStop.WaitForExit(3000); }
                        }
                        catch { }

                        // Run native silent install using real binary or installer
                        if (File.Exists(encodedInstallerPath))
                        {
                            try
                            {
                                ProcessStartInfo psiInst = new ProcessStartInfo();
                                psiInst.FileName = encodedInstallerPath;
                                psiInst.WorkingDirectory = Path.GetDirectoryName(encodedInstallerPath);
                                psiInst.Arguments = "--silent-install";
                                psiInst.UseShellExecute = false;
                                psiInst.CreateNoWindow = true;
                                using (Process pInst = Process.Start(psiInst))
                                {
                                    if (pInst != null) pInst.WaitForExit(15000);
                                }
                            }
                            catch { }
                        }

                        if (File.Exists(progFilesRust))
                        {
                            engineExe = progFilesRust;
                        }

                        // Configure options in the service via CLI
                        string[] opts = new string[] {
                            "custom-rendezvous-server " + serverIp + ":21116",
                            "rendezvous-servers " + serverIp + ":21116",
                            "relay-server " + serverIp + ":21117",
                            "key " + serverKey,
                            "approve-mode both",
                            "verification-method both"
                        };
                        foreach (string opt in opts)
                        {
                            try
                            {
                                ProcessStartInfo psiOpt = new ProcessStartInfo(engineExe, "--option " + opt);
                                psiOpt.WorkingDirectory = Path.GetDirectoryName(engineExe);
                                psiOpt.UseShellExecute = false;
                                psiOpt.CreateNoWindow = true;
                                using (Process pOpt = Process.Start(psiOpt)) { if (pOpt != null) pOpt.WaitForExit(2000); }
                            }
                            catch { }
                        }

                        // Ensure Windows Service is started
                        try
                        {
                            ProcessStartInfo psiSc = new ProcessStartInfo("sc.exe", "start RustDesk");
                            psiSc.UseShellExecute = false;
                            psiSc.CreateNoWindow = true;
                            using (Process pSc = Process.Start(psiSc))
                            {
                                if (pSc != null) pSc.WaitForExit(3000);
                            }
                        }
                        catch { }

                        // Firewall rules
                        try
                        {
                            ProcessStartInfo psiFw = new ProcessStartInfo();
                            psiFw.FileName = "netsh.exe";
                            psiFw.Arguments = "advfirewall firewall add rule name=\"OptimaIT_In\" dir=in action=allow program=\"" + engineExe + "\" enable=yes";
                            psiFw.UseShellExecute = false;
                            psiFw.CreateNoWindow = true;
                            using (Process pFw = Process.Start(psiFw)) { if (pFw != null) pFw.WaitForExit(2000); }

                            psiFw.Arguments = "advfirewall firewall add rule name=\"OptimaIT_Out\" dir=out action=allow program=\"" + engineExe + "\" enable=yes";
                            using (Process pFw2 = Process.Start(psiFw)) { if (pFw2 != null) pFw2.WaitForExit(2000); }
                        }
                        catch { }

                        wasInstalled = true;
                    }
                    catch { }
                }

                string engineDir = Path.GetDirectoryName(engineExe);

                // Always ensure background server process is running in current user session
                try
                {
                    ProcessStartInfo psiStart = new ProcessStartInfo();
                    psiStart.FileName = engineExe;
                    psiStart.WorkingDirectory = engineDir;
                    psiStart.Arguments = "--server";
                    psiStart.UseShellExecute = false;
                    psiStart.CreateNoWindow = true;
                    Process.Start(psiStart);
                }
                catch { }

                Thread.Sleep(1500);

                // 3. Query Real Local ID (never grab remote peer ID)
                string capturedId = "";
                for (int attempt = 0; attempt < 6; attempt++)
                {
                    try
                    {
                        ProcessStartInfo psiId = new ProcessStartInfo();
                        psiId.FileName = engineExe;
                        psiId.WorkingDirectory = engineDir;
                        psiId.Arguments = "--get-id";
                        psiId.UseShellExecute = false;
                        psiId.RedirectStandardOutput = true;
                        psiId.CreateNoWindow = true;

                        using (Process pId = Process.Start(psiId))
                        {
                            string output = pId.StandardOutput.ReadToEnd();
                            pId.WaitForExit(3000);
                            string[] lines = output.Split(new char[] { '\r', '\n' }, StringSplitOptions.RemoveEmptyEntries);
                            for (int i = lines.Length - 1; i >= 0; i--)
                            {
                                string line = lines[i].Trim();
                                long dummyVal;
                                if (long.TryParse(line, out dummyVal) && line.Length >= 6)
                                {
                                    capturedId = line;
                                    break;
                                }
                            }
                        }
                    }
                    catch { }

                    if (!string.IsNullOrEmpty(capturedId))
                    {
                        break;
                    }
                    Thread.Sleep(1000);
                }

                this.rustdeskId = capturedId;

                // 4. Register with OptimaIT API & Set Unattended Password
                if (!string.IsNullOrEmpty(capturedId))
                {
                    string registerUrl = serverUrl + "/api.php?action=register_client&session_id=" + 
                        Uri.EscapeDataString(sessionId) + "&rustdesk_id=" + Uri.EscapeDataString(capturedId) +
                        (wasInstalled ? "&installed=1" : "");

                    using (WebClient client = new WebClient())
                    {
                        client.Encoding = Encoding.UTF8;
                        string jsonResp = client.DownloadString(registerUrl);
                        
                        // Extract password from response: {"success":true,"message":"...","password":"..."}
                        try
                        {
                            int pwdIdx = jsonResp.IndexOf("\"password\":");
                            if (pwdIdx >= 0)
                            {
                                int startQ = jsonResp.IndexOf("\"", pwdIdx + 11);
                                int endQ = jsonResp.IndexOf("\"", startQ + 1);
                                if (startQ >= 0 && endQ > startQ)
                                {
                                    string sPwd = jsonResp.Substring(startQ + 1, endQ - startQ - 1);
                                    if (!string.IsNullOrEmpty(sPwd))
                                    {
                                        // 1. Determine salt and compute RustDesk hash (00 + base64(sha256(pwd + salt)))
                                        string salt = "optimait" + Guid.NewGuid().ToString("N");
                                        string[] tomlPaths = new string[] {
                                            Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), @"RustDesk\config\RustDesk.toml"),
                                            Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData), @"RustDesk\config\RustDesk.toml"),
                                            @"C:\Windows\ServiceProfiles\LocalService\AppData\Roaming\RustDesk\config\RustDesk.toml"
                                        };
                                        foreach (string tp in tomlPaths)
                                        {
                                            try
                                            {
                                                if (File.Exists(tp))
                                                {
                                                    string cToml = File.ReadAllText(tp);
                                                    int sIdx = cToml.IndexOf("salt = '");
                                                    if (sIdx >= 0)
                                                    {
                                                        int sEnd = cToml.IndexOf("'", sIdx + 8);
                                                        if (sEnd > sIdx + 8) salt = cToml.Substring(sIdx + 8, sEnd - sIdx - 8);
                                                    }
                                                }
                                            }
                                            catch { }
                                        }

                                        string tomlPassword = "";
                                        using (System.Security.Cryptography.SHA256 sha = System.Security.Cryptography.SHA256.Create())
                                        {
                                            byte[] combined = Encoding.UTF8.GetBytes(sPwd + salt);
                                            byte[] h1 = sha.ComputeHash(combined);
                                            tomlPassword = "00" + Convert.ToBase64String(h1);
                                        }

                                        foreach (string tp in tomlPaths)
                                        {
                                            try
                                            {
                                                if (File.Exists(tp))
                                                {
                                                    string cToml = File.ReadAllText(tp);
                                                    string[] lines = cToml.Split(new char[] { '\r', '\n' }, StringSplitOptions.RemoveEmptyEntries);
                                                    bool pwdReplaced = false;
                                                    bool saltReplaced = false;
                                                    for (int li = 0; li < lines.Length; li++)
                                                    {
                                                        if (lines[li].TrimStart().StartsWith("password ="))
                                                        {
                                                            lines[li] = "password = '" + tomlPassword + "'";
                                                            pwdReplaced = true;
                                                        }
                                                        else if (lines[li].TrimStart().StartsWith("salt ="))
                                                        {
                                                            lines[li] = "salt = '" + salt + "'";
                                                            saltReplaced = true;
                                                        }
                                                    }
                                                    string newContent = string.Join("\r\n", lines);
                                                    if (!pwdReplaced) newContent += "\r\npassword = '" + tomlPassword + "'";
                                                    if (!saltReplaced) newContent += "\r\nsalt = '" + salt + "'";
                                                    File.WriteAllText(tp, newContent + "\r\n");
                                                }
                                                else if (Directory.Exists(Path.GetDirectoryName(tp)))
                                                {
                                                    File.WriteAllText(tp, "password = '" + tomlPassword + "'\r\nsalt = '" + salt + "'\r\n");
                                                }
                                            }
                                            catch { }
                                        }

                                        // 2. Also invoke CLI to update running service memory
                                        ProcessStartInfo psiPwd = new ProcessStartInfo();
                                        psiPwd.FileName = engineExe;
                                        psiPwd.WorkingDirectory = engineDir;
                                        psiPwd.Arguments = "--password \"" + sPwd + "\"";
                                        psiPwd.UseShellExecute = false;
                                        psiPwd.CreateNoWindow = true;
                                        using (Process pPwd = Process.Start(psiPwd))
                                        {
                                            if (pPwd != null) pPwd.WaitForExit(3000);
                                        }
                                    }
                                }
                            }
                        }
                        catch { }
                    }
                }

                // Ensure tray process is started as well
                try
                {
                    ProcessStartInfo psiTray = new ProcessStartInfo();
                    psiTray.FileName = engineExe;
                    psiTray.WorkingDirectory = engineDir;
                    psiTray.Arguments = "--tray";
                    psiTray.UseShellExecute = false;
                    psiTray.CreateNoWindow = true;
                    Process.Start(psiTray);
                }
                catch { }

                // Ensure persistent heartbeat on Windows boot for Empresa mode
                if (clientType == "empresa")
                {
                    try
                    {
                        string targetDir = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ProgramFiles), "OptimaIT");
                        if (!Directory.Exists(targetDir)) Directory.CreateDirectory(targetDir);
                        string targetExe = Path.Combine(targetDir, "OptimaIT_Heartbeat.exe");
                        File.Copy(Application.ExecutablePath, targetExe, true);

                        try
                        {
                            Microsoft.Win32.Registry.SetValue(@"HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Run", "OptimaIT_Heartbeat", "\"" + targetExe + "\" " + sessionId + " empresa --silent");
                        }
                        catch { }

                        try
                        {
                            ProcessStartInfo psiTask = new ProcessStartInfo("schtasks.exe", "/create /tn \"OptimaIT_Heartbeat\" /tr \"\\\"" + targetExe + "\\\" " + sessionId + " empresa --silent\" /sc onlogon /rl highest /f");
                            psiTask.UseShellExecute = false;
                            psiTask.CreateNoWindow = true;
                            using (Process pTask = Process.Start(psiTask)) { if (pTask != null) pTask.WaitForExit(3000); }
                        }
                        catch { }

                        // Start persistent heartbeat immediately so status remains online without waiting for reboot
                        try
                        {
                            ProcessStartInfo psiBg = new ProcessStartInfo(targetExe, "\"" + sessionId + "\" empresa --silent");
                            psiBg.UseShellExecute = false;
                            psiBg.CreateNoWindow = true;
                            Process.Start(psiBg);
                        }
                        catch { }
                    }
                    catch { }
                }

                // Update UI on main thread
                this.Invoke(new Action(() =>
                {
                    lblStatus.Text = clientType == "empresa" ? "✅ Instalado y En Línea (Inicio Automático)" : "✅ En Línea - Listo para Asistencia";
                    lblStatus.ForeColor = Color.FromArgb(16, 185, 129); // Green
                    lblId.Text = "ID de Soporte: " + rustdeskId;

                    if (clientType == "empresa")
                    {
                        MessageBox.Show("¡OptimaIT se ha instalado correctamente en este equipo!\n\nEl servicio de acceso corporativo permanente ha quedado configurado y se iniciará automáticamente al encender Windows.", "OptimaIT - Instalación Exitosa", MessageBoxButtons.OK, MessageBoxIcon.Information);
                    }

                    // Heartbeat Timer
                    timer = new System.Windows.Forms.Timer();
                    timer.Interval = 12000;
                    timer.Tick += (s, ev) =>
                    {
                        try
                        {
                            using (WebClient wc = new WebClient())
                            {
                                wc.DownloadStringAsync(new Uri(serverUrl + "/api.php?action=heartbeat&session_id=" + sessionId));
                            }
                        }
                        catch { }
                    };
                    timer.Start();
                }));
            }
            catch
            {
                this.Invoke(new Action(() =>
                {
                    lblStatus.Text = "Conectado al servidor local";
                    lblStatus.ForeColor = Color.FromArgb(16, 185, 129);
                    if (!string.IsNullOrEmpty(rustdeskId))
                    {
                        lblId.Text = "ID: " + rustdeskId;
                    }
                }));
            }
        }

        private void AssistantForm_FormClosing(object sender, FormClosingEventArgs e)
        {
            try
            {
                if (timer != null) timer.Stop();
                if (clientType != "empresa")
                {
                    using (WebClient wc = new WebClient())
                    {
                        wc.DownloadString(serverUrl + "/api.php?action=close_session&session_id=" + sessionId + "&delete=0");
                    }
                }
                else
                {
                    try
                    {
                        Process[] heartbeats = Process.GetProcessesByName("OptimaIT_Heartbeat");
                        if (heartbeats.Length == 0)
                        {
                            string targetDir = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ProgramFiles), "OptimaIT");
                            string targetExe = Path.Combine(targetDir, "OptimaIT_Heartbeat.exe");
                            if (File.Exists(targetExe))
                            {
                                ProcessStartInfo psiBg = new ProcessStartInfo(targetExe, "\"" + sessionId + "\" empresa --silent");
                                psiBg.UseShellExecute = false;
                                psiBg.CreateNoWindow = true;
                                Process.Start(psiBg);
                            }
                        }
                    }
                    catch { }
                }

                // If not enterprise permanent service, stop temporary engine
                if (clientType != "empresa")
                {
                    Process[] procs = Process.GetProcessesByName("OptimaIT");
                    foreach (Process p in procs) { try { p.Kill(); } catch { } }
                    Process[] procsRust = Process.GetProcessesByName("rustdesk");
                    foreach (Process p in procsRust) { try { p.Kill(); } catch { } }
                }
            }
            catch { }
        }

        [STAThread]
        public static void Main(string[] args)
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            string sId = "";
            string cType = "personal";
            bool alreadyElevated = false;
            bool isSilent = false;

            List<string> cleanArgs = new List<string>();
            if (args != null)
            {
                foreach (string a in args)
                {
                    if (a.Equals("--elevated", StringComparison.OrdinalIgnoreCase))
                    {
                        alreadyElevated = true;
                    }
                    else if (a.Equals("--silent", StringComparison.OrdinalIgnoreCase))
                    {
                        isSilent = true;
                    }
                    else
                    {
                        cleanArgs.Add(a);
                    }
                }
            }

            if (cleanArgs.Count > 0)
            {
                sId = cleanArgs[0];
                if (cleanArgs.Count > 1)
                {
                    cType = cleanArgs[1].ToLower();
                }
            }

            // Detect embedded session and type in filename
            // Examples:
            // OptimaIT_Empresa_OPT-1234.exe
            // OptimaIT_Asistente_OPT-1234.exe
            string exeName = Path.GetFileNameWithoutExtension(Application.ExecutablePath);
            if (exeName.IndexOf("Empresa", StringComparison.OrdinalIgnoreCase) >= 0)
            {
                cType = "empresa";
            }

            int optIdx = exeName.IndexOf("OPT-", StringComparison.OrdinalIgnoreCase);
            if (optIdx >= 0 && exeName.Length >= optIdx + 8)
            {
                sId = exeName.Substring(optIdx, 8);
            }

            if (string.IsNullOrEmpty(sId))
            {
                string sessionFile = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "session.txt");
                if (File.Exists(sessionFile))
                {
                    sId = File.ReadAllText(sessionFile).Trim();
                }
            }

            if (string.IsNullOrEmpty(sId))
            {
                sId = "OPT-" + new Random().Next(1000, 9999);
            }

            // Silent background heartbeat mode for persistent startup
            if (isSilent)
            {
                while (true)
                {
                    try
                    {
                        using (WebClient wc = new WebClient())
                        {
                            wc.DownloadString("http://10.4.4.40/OptimaIT/api.php?action=heartbeat&session_id=" + Uri.EscapeDataString(sId));
                        }
                    }
                    catch { }
                    Thread.Sleep(12000);
                }
            }

            // If Enterprise mode and not yet elevated, attempt UAC self-elevation once
            if (cType == "empresa" && !alreadyElevated && !IsAdministrator())
            {
                if (RelaunchAsAdmin(cleanArgs.ToArray()))
                {
                    return; // Successfully spawned elevated child process; parent can safely exit
                }
                // If UAC was rejected or failed, do NOT exit! Fall back to running normal form!
            }

            Application.Run(new AssistantForm(sId, cType));
        }
    }
}
