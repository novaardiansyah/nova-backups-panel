<script lang="ts">
  import { router, useForm } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Checkbox } from "@/components/ui/checkbox/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { Select } from "@/components/ui/select/index.js";
  import type { BackupStorageItem, BackupStorageProviderItem } from "@/lib/backup-types.js";

  interface KeyRow {
    key: string;
    value: string;
  }

  interface Props {
    storages: BackupStorageItem[];
    providers: BackupStorageProviderItem[];
    onstatus?: (message: string) => void;
  }

  let { storages, providers, onstatus }: Props = $props();

  let editingId = $state<number | null>(null);
  let deletingId = $state<number | null>(null);
  let keyRows = $state<KeyRow[]>([]);

  const form = useForm({
    name: "",
    slug: "",
    provider_id: "" as string | number,
    active: true,
    keys: {} as Record<string, string>,
  });

  function slugify(value: string): string {
    return value
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }

  function addKeyRow() {
    keyRows = [...keyRows, { key: "", value: "" }];
  }

  function removeKeyRow(index: number) {
    keyRows = keyRows.filter((_, i) => i !== index);
  }

  function buildKeysObject(): Record<string, string> {
    const result: Record<string, string> = {};
    for (const row of keyRows) {
      const k = row.key.trim();
      if (k !== "") {
        result[k] = row.value;
      }
    }
    return result;
  }

  function startEdit(storage: BackupStorageItem) {
    editingId = storage.id;
    form.clearErrors();
    form.name = storage.name;
    form.slug = storage.slug;
    form.provider_id = storage.providerId ?? "";
    form.active = storage.active;
    keyRows = Object.entries(storage.keys ?? {}).map(([key, value]) => ({
      key,
      value: String(value ?? ""),
    }));
  }

  function resetForm() {
    editingId = null;
    form.reset();
    form.active = true;
    form.clearErrors();
    keyRows = [];
  }

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.keys = buildKeysObject();

    const options = {
      preserveScroll: true,
      onSuccess: (page: { props: unknown }) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
        resetForm();
      },
    };

    if (editingId !== null) {
      form.put(`/client/backup-storages/${editingId}`, options);
      return;
    }

    form.post("/client/backup-storages", options);
  }

  function handleDelete(id: number) {
    deletingId = id;
    router.delete(`/client/backup-storages/${id}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
        if (editingId === id) {
          resetForm();
        }
      },
      onFinish: () => {
        deletingId = null;
      },
    });
  }
</script>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex items-start gap-3">
      <span
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
          <rect width="20" height="8" x="2" y="2" rx="2" ry="2"/>
          <rect width="20" height="8" x="2" y="14" rx="2" ry="2"/>
          <line x1="6" x2="6.01" y1="6" y2="6"/>
          <line x1="6" x2="6.01" y1="18" y2="18"/>
        </svg>
      </span>
      <div class="min-w-0">
        <h2 class="text-lg font-semibold tracking-tight text-ink">Backup Storage</h2>
        <p class="mt-1 text-sm leading-6 text-muted">
          Configure storage destinations and server targets used by backup schedules.
        </p>
      </div>
    </div>
  </div>

  <form
    class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6"
    onsubmit={handleSubmit}
    novalidate
  >
    <div class="flex items-center justify-between gap-2">
      <h3 class="text-sm font-semibold text-ink">
        {editingId !== null ? "Edit Backup Storage" : "Add Backup Storage"}
      </h3>
      {#if editingId !== null}
        <button
          type="button"
          onclick={resetForm}
          class="cursor-pointer text-xs font-medium text-accent hover:underline"
        >
          Cancel edit
        </button>
      {/if}
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
      <div class="grid gap-2">
        <Label for="storage-name">Name</Label>
        <Input
          id="storage-name"
          type="text"
          placeholder="Primary Storage"
          bind:value={form.name}
          oninput={() => {
            if (editingId === null) {
              form.slug = slugify(form.name);
            }
          }}
          disabled={form.processing}
          aria-invalid={Boolean(form.errors.name)}
        />
        {#if form.errors.name}
          <p class="text-xs text-destructive">{form.errors.name}</p>
        {/if}
      </div>

      <div class="grid gap-2">
        <Label for="storage-slug">Slug</Label>
        <Input
          id="storage-slug"
          type="text"
          placeholder="primary-storage"
          bind:value={form.slug}
          disabled={form.processing}
          aria-invalid={Boolean(form.errors.slug)}
        />
        {#if form.errors.slug}
          <p class="text-xs text-destructive">{form.errors.slug}</p>
        {/if}
      </div>

      <div class="grid gap-2">
        <Label for="storage-provider">Provider</Label>
        <Select
          id="storage-provider"
          bind:value={form.provider_id}
          disabled={form.processing}
          aria-invalid={Boolean(form.errors.provider_id)}
        >
          <option value="">None (Local / Server)</option>
          {#each providers as provider (provider.id)}
            <option value={provider.id}>{provider.name}</option>
          {/each}
        </Select>
        {#if form.errors.provider_id}
          <p class="text-xs text-destructive">{form.errors.provider_id}</p>
        {/if}
      </div>

      <div class="grid gap-2">
        <Label>Status</Label>
        <label class="flex h-9 cursor-pointer items-center gap-2.5 rounded-md border border-input bg-page-elevated px-3 text-sm text-ink shadow-xs">
          <Checkbox bind:checked={form.active} disabled={form.processing} />
          <span>Active</span>
        </label>
      </div>
    </div>

    <div class="mt-5 border-t border-line pt-4">
      <div class="flex items-center justify-between gap-2">
        <Label>Storage Keys</Label>
        <Button type="button" variant="outline" size="sm" onclick={addKeyRow}>
          Add key
        </Button>
      </div>

      {#if keyRows.length > 0}
        <div class="mt-3 space-y-2">
          {#each keyRows as row, index (index)}
            <div class="grid grid-cols-[1fr_1fr_auto] gap-2">
              <Input type="text" placeholder="Key" bind:value={row.key} />
              <Input type="text" placeholder="Value" bind:value={row.value} />
              <Button type="button" variant="ghost" size="sm" onclick={() => removeKeyRow(index)}>
                Remove
              </Button>
            </div>
          {/each}
        </div>
      {/if}
    </div>

    <div class="mt-5 flex">
      <Button type="submit" disabled={form.processing}>
        {form.processing
          ? "Saving…"
          : editingId !== null
            ? "Update storage"
            : "Create storage"}
      </Button>
    </div>
  </form>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if storages.length === 0}
      <p class="text-sm text-muted">No backup storages configured yet.</p>
    {:else}
      <div class="space-y-3">
        {#each storages as storage (storage.id)}
          <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-line bg-page p-4">
            <div class="min-w-0 space-y-1">
              <div class="flex items-center gap-2">
                <p class="text-sm font-semibold text-ink">{storage.name}</p>
                <span
                  class="rounded-full px-2 py-0.5 text-[11px] font-medium {storage.active
                    ? 'bg-accent-soft text-accent'
                    : 'bg-surface-muted text-muted'}"
                >
                  {storage.active ? "Active" : "Inactive"}
                </span>
              </div>
              <p class="text-xs text-muted">
                Slug: <span class="font-mono">{storage.slug}</span> &middot; Provider: {storage.providerName ?? "Local"} &middot; Keys: {Object.keys(storage.keys ?? {}).length}
              </p>
            </div>

            <div class="flex items-center gap-2">
              <Button type="button" variant="outline" size="sm" onclick={() => startEdit(storage)}>
                Edit
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                disabled={deletingId === storage.id}
                onclick={() => handleDelete(storage.id)}
              >
                {deletingId === storage.id ? "Deleting…" : "Delete"}
              </Button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>
