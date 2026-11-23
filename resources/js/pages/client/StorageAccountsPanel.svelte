<script lang="ts">
  import { router, useForm } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import type { LocalePreference } from "@/lib/theme.js";
  import type { StorageAccountItem } from "@/lib/v2-types.js";

  interface Props {
    storageAccounts: StorageAccountItem[];
    locale?: LocalePreference;
    onstatus?: (message: string) => void;
  }

  let { storageAccounts = [], locale = "en", onstatus }: Props = $props();

  let editingAccount = $state<StorageAccountItem | null>(null);
  const form = useForm({ name: "" });
  const isId = $derived(locale === "id");

  function connectGoogleDrive() {
    window.location.href = "/client/storage-accounts/google/redirect";
  }

  function openEdit(account: StorageAccountItem) {
    editingAccount = account;
    form.name = account.name;
    form.clearErrors();
  }

  function handleUpdate(event: SubmitEvent) {
    event.preventDefault();
    if (!editingAccount) return;
    form.put(`/client/storage-accounts/${editingAccount.uid}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        editingAccount = null;
        const msg = (page.props as { status?: string | null }).status;
        if (msg) onstatus?.(msg);
      },
    });
  }

  function handleDelete(account: StorageAccountItem) {
    router.delete(`/client/storage-accounts/${account.uid}`, {
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
            <ellipse cx="12" cy="5" rx="9" ry="3"/>
            <path d="M3 5v14a9 3 0 0 0 18 0V5"/>
            <path d="M3 12a9 3 0 0 0 18 0"/>
          </svg>
        </span>
        <div class="min-w-0">
          <h2 class="text-lg font-semibold tracking-tight text-ink">
            {isId ? "Akun Penyimpanan Cloud" : "Cloud Storage Accounts"}
          </h2>
          <p class="mt-1 text-sm leading-6 text-muted">
            {isId
              ? "Hubungkan satu atau beberapa akun Google Drive sebagai tujuan penyimpanan arsip backup."
              : "Connect one or more Google Drive accounts as destinations for encrypted backup archives."}
          </p>
        </div>
      </div>

      <Button type="button" onclick={connectGoogleDrive}>
        {isId ? "Hubungkan Google Drive" : "Connect Google Drive"}
      </Button>
    </div>
  </div>

  {#if editingAccount}
    <form class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6" onsubmit={handleUpdate} novalidate>
      <h3 class="text-base font-semibold text-ink">
        {isId ? "Ubah Nama Akun" : "Rename Storage Account"}
      </h3>
      <div class="mt-4 grid gap-2 sm:max-w-md">
        <Label for="storage-name">{isId ? "Nama Tampilan" : "Display Name"}</Label>
        <Input id="storage-name" type="text" bind:value={form.name} />
        {#if form.errors.name}
          <p class="text-xs text-danger">{form.errors.name}</p>
        {/if}
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <Button type="button" variant="outline" onclick={() => (editingAccount = null)}>
          {isId ? "Batal" : "Cancel"}
        </Button>
        <Button type="submit" disabled={form.processing}>
          {isId ? "Simpan" : "Save"}
        </Button>
      </div>
    </form>
  {/if}

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if storageAccounts.length === 0}
      <p class="text-sm text-muted">
        {isId
          ? "Belum ada akun Google Drive terhubung. Klik tombol di atas untuk menghubungkan akun."
          : "No Google Drive accounts connected yet. Click the button above to authorize an account."}
      </p>
    {:else}
      <div class="divide-y divide-line">
        {#each storageAccounts as account (account.uid)}
          <div class="flex flex-wrap items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
            <div class="min-w-0 space-y-1">
              <div class="flex items-center gap-2.5">
                <span class="h-2 w-2 rounded-full {account.status === 'connected' ? 'bg-accent' : 'bg-danger'}"></span>
                <p class="text-sm font-semibold text-ink">{account.name}</p>
                <span class="rounded border border-line bg-page px-2 py-0.5 font-mono text-[11px] text-muted">
                  {account.provider}
                </span>
              </div>
              <p class="text-xs text-muted">
                {account.accountEmail} · UID: <span class="font-mono">{account.uid}</span>
              </p>
            </div>
            <div class="flex items-center gap-2">
              <Button type="button" variant="outline" size="sm" onclick={() => openEdit(account)}>
                {isId ? "Ubah" : "Rename"}
              </Button>
              <Button type="button" variant="outline" size="sm" class="text-danger hover:bg-danger-soft" onclick={() => handleDelete(account)}>
                {isId ? "Hapus" : "Remove"}
              </Button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>
