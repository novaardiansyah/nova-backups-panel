<script lang="ts">
  import { router } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Checkbox } from "@/components/ui/checkbox/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { PasswordInput } from "@/components/ui/password-input/index.js";
  import { Select } from "@/components/ui/select/index.js";
  import type { LocalePreference } from "@/lib/theme.js";
  import type {
    AgentItem,
    BackupProfileItem,
    StorageAccountItem,
  } from "@/lib/v2-types.js";

  interface Props {
    profiles: BackupProfileItem[];
    agents: AgentItem[];
    storageAccounts: StorageAccountItem[];
    locale?: LocalePreference;
    onstatus?: (message: string) => void;
  }

  let {
    profiles = [],
    agents = [],
    storageAccounts = [],
    locale = "en",
    onstatus,
  }: Props = $props();

  const isId = $derived(locale === "id");

  let showForm = $state(false);
  let editingUid = $state<string | null>(null);
  let submitting = $state(false);
  let errorMsg = $state<string | null>(null);

  let name = $state("");
  let description = $state("");
  let agentUid = $state("");
  let isActive = $state(true);
  let encryptArchive = $state(true);
  let encryptFileNames = $state(true);
  let rarPassword = $state("");
  let compressionLevel = $state("5");
  let keepLocalDefault = $state(false);

  const agentOptions = $derived(
    agents.map((a) => ({ value: a.uid, label: `${a.name} (${a.hostname})` })),
  );

  const storageOptions = $derived([
    { value: "", label: isId ? "— Tidak ada (lokal saja) —" : "— None (local only) —" },
    ...storageAccounts.map((acc) => ({ value: acc.uid, label: `${acc.name} (${acc.accountEmail})` })),
  ]);

  const scheduleOptions = [
    { value: "hourly", label: "Hourly" },
    { value: "daily", label: "Daily" },
    { value: "weekly", label: "Weekly" },
    { value: "custom", label: "Custom" },
  ];

  let sourceName = $state("Application Source");
  let sourcePath = $state("/var/www/html");
  let exclusionsText = $state("node_modules/*\nstorage/logs/*\n.git/*");

  let storageAccountUid = $state("");
  let remoteFolderId = $state("root");

  let scheduleEnabled = $state(true);
  let scheduleType = $state("daily");
  let cronExpression = $state("0 2 * * *");
  let timezone = $state("Asia/Jakarta");

  function openCreate() {
    editingUid = null;
    name = "";
    description = "";
    agentUid = agents[0]?.uid ?? "";
    isActive = true;
    encryptArchive = true;
    encryptFileNames = true;
    rarPassword = "";
    compressionLevel = "5";
    keepLocalDefault = false;
    sourceName = "Application Source";
    sourcePath = "/var/www/html";
    exclusionsText = "node_modules/*\nstorage/logs/*\n.git/*";
    storageAccountUid = storageAccounts[0]?.uid ?? "";
    remoteFolderId = "root";
    scheduleEnabled = true;
    scheduleType = "daily";
    cronExpression = "0 2 * * *";
    timezone = "Asia/Jakarta";
    errorMsg = null;
    showForm = true;
  }

  function openEdit(profile: BackupProfileItem) {
    editingUid = profile.uid;
    name = profile.name;
    description = profile.description ?? "";
    agentUid = profile.agentUid ?? agents[0]?.uid ?? "";
    isActive = profile.isActive;
    encryptArchive = profile.encryptArchive;
    encryptFileNames = profile.encryptFileNames;
    rarPassword = "";
    compressionLevel = String(profile.compressionLevel);
    keepLocalDefault = profile.keepLocalDefault;
    const firstSource = profile.sources[0];
    sourceName = firstSource?.name ?? "Application Source";
    sourcePath = firstSource?.path ?? "/var/www/html";
    exclusionsText = (firstSource?.exclusions ?? []).join("\n");
    const firstDest = profile.destinations[0];
    storageAccountUid = firstDest?.storageAccountUid ?? "";
    remoteFolderId = firstDest?.remoteFolderId ?? "root";
    scheduleEnabled = profile.schedule?.isEnabled ?? false;
    scheduleType = profile.schedule?.scheduleType ?? "daily";
    cronExpression = profile.schedule?.cronExpression ?? "0 2 * * *";
    timezone = profile.schedule?.timezone ?? "Asia/Jakarta";
    errorMsg = null;
    showForm = true;
  }

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    if (submitting) return;
    submitting = true;
    errorMsg = null;

    const exclusions = exclusionsText
      .split("\n")
      .map((line) => line.trim())
      .filter((line) => line !== "");

    const payload = {
      name,
      description,
      agent_uid: agentUid,
      is_active: isActive,
      encrypt_archive: encryptArchive,
      encrypt_file_names: encryptFileNames,
      rar_password: rarPassword || null,
      compression_level: Number(compressionLevel),
      keep_local_default: keepLocalDefault,
      sources: [{ name: sourceName, path: sourcePath, exclusions }],
      destinations: storageAccountUid
        ? [{ storage_account_uid: storageAccountUid, remote_folder_id: remoteFolderId || "root", is_required: true }]
        : [],
      schedule_enabled: scheduleEnabled,
      schedule_type: scheduleType,
      cron_expression: cronExpression,
      timezone,
    };

    const options = {
      preserveScroll: true,
      onSuccess: (page: { props: Record<string, unknown> }) => {
        showForm = false;
        const msg = page.props.status as string | null;
        if (msg) onstatus?.(msg);
      },
      onError: (errors: Record<string, string>) => {
        errorMsg = Object.values(errors)[0] ?? "Validation failed.";
      },
      onFinish: () => {
        submitting = false;
      },
    };

    if (editingUid) {
      router.put(`/client/backup-profiles/${editingUid}`, payload, options);
    } else {
      router.post("/client/backup-profiles", payload, options);
    }
  }

  function handleRunNow(profile: BackupProfileItem) {
    router.post(`/client/backup-profiles/${profile.uid}/run`, {}, {
      preserveScroll: true,
      onSuccess: (page) => {
        const msg = (page.props as { status?: string | null }).status;
        if (msg) onstatus?.(msg);
      },
    });
  }

  function handleDelete(profile: BackupProfileItem) {
    router.delete(`/client/backup-profiles/${profile.uid}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        const msg = (page.props as { status?: string | null }).status;
        if (msg) onstatus?.(msg);
      },
    });
  }
</script>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="flex items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" x2="12" y1="15" y2="3"/>
          </svg>
        </span>
        <div class="min-w-0">
          <h2 class="text-lg font-semibold tracking-tight text-ink">
            {isId ? "Profil Backup" : "Backup Profiles"}
          </h2>
          <p class="mt-1 text-sm leading-6 text-muted">
            {isId
              ? "Atur sumber direktori, aturan pengecualian glob, enkripsi RAR, tujuan Google Drive, dan jadwal cron."
              : "Configure source directories, glob exclusions, encrypted RAR settings, Google Drive destinations, and cron schedules."}
          </p>
        </div>
      </div>

      <Button type="button" onclick={openCreate} disabled={agents.length === 0}>
        {isId ? "Buat Profil" : "Create Profile"}
      </Button>
    </div>
    {#if agents.length === 0}
      <p class="mt-3 text-xs text-muted">
        {isId
          ? "Daftarkan minimal satu agen terlebih dahulu sebelum membuat profil backup."
          : "Register at least one agent first before creating a backup profile."}
      </p>
    {/if}
  </div>

  {#if showForm}
    <form class="space-y-4 rounded-xl border border-line bg-page-elevated p-5 sm:p-6" onsubmit={handleSubmit} novalidate>
      <h3 class="text-base font-semibold text-ink">
        {editingUid ? (isId ? "Ubah Profil Backup" : "Edit Backup Profile") : (isId ? "Profil Backup Baru" : "New Backup Profile")}
      </h3>

      {#if errorMsg}
        <p class="rounded-md border border-danger/40 bg-danger-soft px-3 py-2 text-xs text-danger">{errorMsg}</p>
      {/if}

      <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
          <Label for="profile-name">{isId ? "Nama Profil" : "Profile Name"}</Label>
          <Input id="profile-name" type="text" placeholder="Production Backup" bind:value={name} />
        </div>
        <div class="grid gap-2">
          <Label for="profile-agent">{isId ? "Agen Target" : "Target Agent"}</Label>
          <Select id="profile-agent" bind:value={agentUid} options={agentOptions} />
        </div>
      </div>

      <div class="grid gap-2">
        <Label for="profile-desc">{isId ? "Deskripsi" : "Description"}</Label>
        <Input id="profile-desc" type="text" placeholder={isId ? "Opsional" : "Optional"} bind:value={description} />
      </div>

      <div class="grid gap-4 border-t border-line pt-4 sm:grid-cols-2">
        <div class="grid gap-2">
          <Label for="source-name">{isId ? "Nama Sumber" : "Source Label"}</Label>
          <Input id="source-name" type="text" bind:value={sourceName} />
        </div>
        <div class="grid gap-2">
          <Label for="source-path">{isId ? "Path Direktori Sumber" : "Source Path"}</Label>
          <Input id="source-path" type="text" bind:value={sourcePath} />
        </div>
      </div>

      <div class="grid gap-2">
        <Label for="exclusions">{isId ? "Pengecualian Glob (satu per baris)" : "Glob Exclusions (one per line)"}</Label>
        <textarea
          id="exclusions"
          rows="3"
          class="w-full rounded-md border border-line bg-page px-3 py-2 font-mono text-xs text-ink focus:outline-none focus:ring-2 focus:ring-accent"
          bind:value={exclusionsText}
        ></textarea>
      </div>

      <div class="grid gap-4 border-t border-line pt-4 sm:grid-cols-2">
        <div class="grid gap-2">
          <Label for="dest-account">{isId ? "Tujuan Google Drive" : "Google Drive Destination"}</Label>
          <Select id="dest-account" bind:value={storageAccountUid} options={storageOptions} />
        </div>
        <div class="grid gap-2">
          <Label for="dest-folder">{isId ? "ID Folder Google Drive" : "Google Drive Folder ID"}</Label>
          <Input id="dest-folder" type="text" placeholder="root" bind:value={remoteFolderId} />
        </div>
      </div>

      <div class="grid gap-4 border-t border-line pt-4 sm:grid-cols-2">
        <div class="grid gap-2">
          <Label for="rar-pass">
            {isId ? "Password Enkripsi RAR" : "RAR Encryption Password"}
            {#if editingUid}
              <span class="font-normal text-faint">({isId ? "kosongkan jika tetap" : "leave blank to keep"})</span>
            {/if}
          </Label>
          <PasswordInput id="rar-pass" placeholder="••••••••" bind:value={rarPassword} />
        </div>
        <div class="grid gap-2">
          <Label for="comp-level">{isId ? "Level Kompresi (0–5)" : "Compression Level (0–5)"}</Label>
          <Input id="comp-level" type="number" min="0" max="5" bind:value={compressionLevel} />
        </div>
      </div>

      <div class="flex flex-wrap gap-6 pt-1">
        <label class="flex cursor-pointer items-center gap-2 text-sm text-ink">
          <Checkbox bind:checked={encryptArchive} />
          {isId ? "Enkripsi isi arsip RAR" : "Encrypt RAR contents"}
        </label>
        <label class="flex cursor-pointer items-center gap-2 text-sm text-ink">
          <Checkbox bind:checked={encryptFileNames} />
          {isId ? "Enkripsi nama file" : "Encrypt filenames"}
        </label>
        <label class="flex cursor-pointer items-center gap-2 text-sm text-ink">
          <Checkbox bind:checked={keepLocalDefault} />
          {isId ? "Simpan salinan lokal" : "Keep local copy"}
        </label>
      </div>

      <div class="grid gap-4 border-t border-line pt-4 sm:grid-cols-3">
        <label class="flex cursor-pointer items-center gap-2 text-sm text-ink sm:col-span-3">
          <Checkbox bind:checked={scheduleEnabled} />
          {isId ? "Aktifkan jadwal otomatis (cron)" : "Enable automated cron schedule"}
        </label>
        <div class="grid gap-2">
          <Label for="sched-type">{isId ? "Tipe Jadwal" : "Schedule Type"}</Label>
          <Select id="sched-type" bind:value={scheduleType} options={scheduleOptions} />
        </div>
        <div class="grid gap-2">
          <Label for="cron-expr">Cron</Label>
          <Input id="cron-expr" type="text" placeholder="0 2 * * *" bind:value={cronExpression} />
        </div>
        <div class="grid gap-2">
          <Label for="sched-tz">Timezone</Label>
          <Input id="sched-tz" type="text" placeholder="Asia/Jakarta" bind:value={timezone} />
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <Button type="button" variant="outline" onclick={() => (showForm = false)}>
          {isId ? "Batal" : "Cancel"}
        </Button>
        <Button type="submit" disabled={submitting}>
          {submitting ? (isId ? "Menyimpan..." : "Saving...") : (isId ? "Simpan Profil" : "Save Profile")}
        </Button>
      </div>
    </form>
  {/if}

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if profiles.length === 0}
      <p class="text-sm text-muted">
        {isId ? "Belum ada profil backup." : "No backup profiles configured yet."}
      </p>
    {:else}
      <div class="divide-y divide-line">
        {#each profiles as profile (profile.uid)}
          <div class="flex flex-wrap items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
            <div class="min-w-0 space-y-1">
              <div class="flex flex-wrap items-center gap-2">
                <span class="h-2 w-2 rounded-full {profile.isActive ? 'bg-accent' : 'bg-faint'}"></span>
                <p class="text-sm font-semibold text-ink">{profile.name}</p>
                <span class="rounded border border-line bg-page px-2 py-0.5 font-mono text-[11px] text-muted">
                  v{profile.latestVersion} · RAR
                </span>
                {#if profile.schedule?.isEnabled}
                  <span class="rounded border border-line bg-page px-2 py-0.5 font-mono text-[11px] text-muted">
                    {profile.schedule.cronExpression}
                  </span>
                {/if}
              </div>
              <p class="text-xs text-muted">
                {isId ? "Agen" : "Agent"}: {profile.agentName ?? "—"} ·
                {profile.sources.length} {isId ? "sumber" : "source(s)"} ·
                {profile.destinations.length} {isId ? "tujuan" : "destination(s)"}
              </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <Button type="button" size="sm" onclick={() => handleRunNow(profile)}>
                {isId ? "Jalankan" : "Run Now"}
              </Button>
              <Button type="button" variant="outline" size="sm" onclick={() => openEdit(profile)}>
                {isId ? "Ubah" : "Edit"}
              </Button>
              <Button type="button" variant="outline" size="sm" class="text-danger hover:bg-danger-soft" onclick={() => handleDelete(profile)}>
                {isId ? "Hapus" : "Delete"}
              </Button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>
