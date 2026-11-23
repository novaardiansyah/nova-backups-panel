<script lang="ts">
  import { router, useForm } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import type { LocalePreference } from "@/lib/theme.js";
  import type { AgentItem } from "@/lib/v2-types.js";

  interface Props {
    agents: AgentItem[];
    agentToken?: string | null;
    locale?: LocalePreference;
    onstatus?: (message: string) => void;
  }

  let { agents = [], agentToken = null, locale = "en", onstatus }: Props = $props();

  let showCreate = $state(false);
  let editingAgent = $state<AgentItem | null>(null);
  let copiedToken = $state(false);
  let dismissedToken = $state<string | null>(null);

  const visibleToken = $derived(agentToken && agentToken !== dismissedToken ? agentToken : null);

  const form = useForm({
    name: "",
    hostname: "",
  });

  const isId = $derived(locale === "id");

  function openCreate() {
    editingAgent = null;
    form.name = "";
    form.hostname = "";
    form.clearErrors();
    showCreate = true;
  }

  function openEdit(agent: AgentItem) {
    showCreate = false;
    editingAgent = agent;
    form.name = agent.name;
    form.hostname = agent.hostname;
    form.clearErrors();
  }

  function closeForm() {
    showCreate = false;
    editingAgent = null;
    form.reset();
  }

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    if (editingAgent) {
      form.put(`/client/agents/${editingAgent.uid}`, {
        preserveScroll: true,
        onSuccess: (page) => {
          closeForm();
          const msg = (page.props as { status?: string | null }).status;
          if (msg) onstatus?.(msg);
        },
      });
    } else {
      form.post("/client/agents", {
        preserveScroll: true,
        onSuccess: (page) => {
          closeForm();
          const msg = (page.props as { status?: string | null }).status;
          if (msg) onstatus?.(msg);
        },
      });
    }
  }

  function handleRotateToken(agent: AgentItem) {
    router.post(`/client/agents/${agent.uid}/rotate-token`, {}, {
      preserveScroll: true,
      onSuccess: (page) => {
        dismissedToken = null;
        const msg = (page.props as { status?: string | null }).status;
        if (msg) onstatus?.(msg);
      },
    });
  }

  function handleDelete(agent: AgentItem) {
    router.delete(`/client/agents/${agent.uid}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        const msg = (page.props as { status?: string | null }).status;
        if (msg) onstatus?.(msg);
      },
    });
  }

  async function copyToken(token: string) {
    try {
      await navigator.clipboard.writeText(token);
      copiedToken = true;
      setTimeout(() => (copiedToken = false), 2000);
    } catch {
      copiedToken = false;
    }
  }

  function formatTime(value: string | null): string {
    if (!value) return "—";
    const d = new Date(value);
    return Number.isNaN(d.getTime()) ? "—" : d.toLocaleString();
  }
</script>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="flex items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
            <rect width="20" height="8" x="2" y="2" rx="2" ry="2"/>
            <rect width="20" height="8" x="2" y="14" rx="2" ry="2"/>
            <line x1="6" x2="6.01" y1="6" y2="6"/>
            <line x1="6" x2="6.01" y1="18" y2="18"/>
          </svg>
        </span>
        <div class="min-w-0">
          <h2 class="text-lg font-semibold tracking-tight text-ink">
            {isId ? "Agen Backup" : "Backup Agents"}
          </h2>
          <p class="mt-1 text-sm leading-6 text-muted">
            {isId
              ? "Daftarkan server target dan kelola token autentikasi untuk agen Golang."
              : "Register target servers and manage API authentication tokens for the Golang agent."}
          </p>
        </div>
      </div>

      <Button type="button" onclick={openCreate}>
        {isId ? "Tambah Agen" : "Add Agent"}
      </Button>
    </div>
  </div>

  {#if visibleToken}
    <div class="rounded-xl border border-accent/40 bg-page-elevated p-5 sm:p-6">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-sm font-semibold text-ink">
            {isId ? "Token API Agen (tampil sekali)" : "Agent API Token (shown once)"}
          </p>
          <p class="mt-1 text-xs text-muted">
            {isId
              ? "Simpan token ini di variabel lingkungan AGENT_TOKEN pada container agen Anda."
              : "Store this token in your agent container's AGENT_TOKEN environment variable."}
          </p>
        </div>
        <Button type="button" variant="ghost" size="sm" onclick={() => (dismissedToken = visibleToken)}>
          {isId ? "Tutup" : "Dismiss"}
        </Button>
      </div>
      <div class="mt-3 flex flex-wrap items-center gap-2">
        <code class="flex-1 break-all rounded-md border border-line bg-page px-3 py-2 font-mono text-xs text-ink">
          {visibleToken}
        </code>
        <Button type="button" variant="outline" size="sm" onclick={() => copyToken(visibleToken)}>
          {copiedToken ? (isId ? "Tersalin" : "Copied") : (isId ? "Salin" : "Copy")}
        </Button>
      </div>
    </div>
  {/if}

  {#if showCreate || editingAgent}
    <form class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6" onsubmit={handleSubmit} novalidate>
      <h3 class="text-base font-semibold text-ink">
        {editingAgent ? (isId ? "Ubah Agen" : "Edit Agent") : (isId ? "Agen Baru" : "New Agent")}
      </h3>
      <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
          <Label for="agent-name">{isId ? "Nama Agen" : "Agent Name"}</Label>
          <Input id="agent-name" type="text" placeholder="Production Server" bind:value={form.name} />
          {#if form.errors.name}
            <p class="text-xs text-danger">{form.errors.name}</p>
          {/if}
        </div>
        <div class="grid gap-2">
          <Label for="agent-hostname">{isId ? "Hostname" : "Hostname"}</Label>
          <Input id="agent-hostname" type="text" placeholder="server-prod-01" bind:value={form.hostname} />
          {#if form.errors.hostname}
            <p class="text-xs text-danger">{form.errors.hostname}</p>
          {/if}
        </div>
      </div>
      <div class="mt-4 flex flex-wrap justify-end gap-2">
        <Button type="button" variant="outline" onclick={closeForm}>
          {isId ? "Batal" : "Cancel"}
        </Button>
        <Button type="submit" disabled={form.processing}>
          {form.processing ? (isId ? "Menyimpan..." : "Saving...") : (isId ? "Simpan" : "Save")}
        </Button>
      </div>
    </form>
  {/if}

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if agents.length === 0}
      <p class="text-sm text-muted">
        {isId ? "Belum ada agen terdaftar." : "No agents registered yet."}
      </p>
    {:else}
      <div class="divide-y divide-line">
        {#each agents as agent (agent.uid)}
          <div class="flex flex-wrap items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
            <div class="min-w-0 space-y-1">
              <div class="flex items-center gap-2.5">
                <span class="h-2 w-2 rounded-full {agent.status === 'online' ? 'bg-accent' : 'bg-faint'}"></span>
                <p class="text-sm font-semibold text-ink">{agent.name}</p>
                <span class="rounded border border-line bg-page px-2 py-0.5 font-mono text-[11px] text-muted">
                  {agent.hostname}
                </span>
              </div>
              <p class="text-xs text-muted">
                UID: <span class="font-mono">{agent.uid}</span> ·
                {isId ? "Terakhir terlihat" : "Last seen"}: {formatTime(agent.lastSeenAt)} ·
                {agent.profilesCount} {isId ? "profil" : "profiles"}
              </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <Button type="button" variant="outline" size="sm" onclick={() => handleRotateToken(agent)}>
                {isId ? "Reset Token" : "Rotate Token"}
              </Button>
              <Button type="button" variant="outline" size="sm" onclick={() => openEdit(agent)}>
                {isId ? "Ubah" : "Edit"}
              </Button>
              <Button type="button" variant="outline" size="sm" class="text-danger hover:bg-danger-soft" onclick={() => handleDelete(agent)}>
                {isId ? "Hapus" : "Delete"}
              </Button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>
