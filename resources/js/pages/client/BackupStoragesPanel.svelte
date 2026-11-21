<script lang="ts">
  import { router, useForm } from "@inertiajs/svelte";
  import { slide } from "svelte/transition";
  import { Button } from "@/components/ui/button/index.js";
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card/index.js";
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

  let isDialogOpen = $state(false);
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

  function openCreate() {
    editingId = null;
    form.reset();
    form.active = true;
    form.clearErrors();
    keyRows = [];
    isDialogOpen = true;
  }

  function openEdit(storage: BackupStorageItem) {
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
    isDialogOpen = true;
  }

  function closeDialog() {
    isDialogOpen = false;
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
        closeDialog();
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
          closeDialog();
        }
      },
      onFinish: () => {
        deletingId = null;
      },
    });
  }
</script>

<svelte:window
  onkeydown={(e) => {
    if (e.key === "Escape" && isDialogOpen) {
      closeDialog();
    }
  }}
/>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
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

      <Button type="button" onclick={openCreate} class="shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5 size-4">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Storage
      </Button>
    </div>
  </div>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if storages.length === 0}
      <div class="rounded-lg border border-dashed border-line p-8 text-center">
        <p class="text-sm font-medium text-ink">No backup storages configured yet.</p>
        <p class="mt-1 text-xs text-muted">Configure a storage destination to start storing backups.</p>
        <div class="mt-4">
          <Button type="button" size="sm" onclick={openCreate}>
            Add Storage
          </Button>
        </div>
      </div>
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
              <Button type="button" variant="outline" size="sm" onclick={() => openEdit(storage)}>
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

{#if isDialogOpen}
  <div
    role="dialog"
    aria-modal="true"
    aria-labelledby="storage-dialog-title"
    tabindex="-1"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
  >
    <button
      type="button"
      class="fixed inset-0 bg-black/50 backdrop-blur-xs"
      onclick={closeDialog}
      aria-label="Close modal"
      tabindex="-1"
    ></button>

    <div class="relative z-10 my-8 w-full max-w-2xl">
      <Card class="shadow-lg max-h-[90vh] flex flex-col">
        <CardHeader class="shrink-0">
          <div class="flex items-center justify-between gap-2">
            <CardTitle id="storage-dialog-title">
              {editingId !== null ? "Edit Backup Storage" : "Add Backup Storage"}
            </CardTitle>
            <button
              type="button"
              onclick={closeDialog}
              class="cursor-pointer rounded-md p-1 text-muted hover:text-ink"
              aria-label="Close"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <CardDescription>
            {editingId !== null
              ? "Update storage configuration and credentials."
              : "Configure storage destinations and server targets used by backup schedules."}
          </CardDescription>
        </CardHeader>

        <CardContent class="overflow-y-auto">
          <form onsubmit={handleSubmit} novalidate class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
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
                  options={[
                    { value: "", label: "None (Local / Server)" },
                    ...providers.map((provider) => ({
                      value: provider.id,
                      label: provider.name,
                    })),
                  ]}
                />
                {#if form.errors.provider_id}
                  <p class="text-xs text-destructive">{form.errors.provider_id}</p>
                {/if}
              </div>

              <div class="grid gap-2">
                <Label>Status</Label>
                <label class="flex h-9 cursor-pointer items-center gap-2.5 rounded-md border border-input bg-page-elevated px-3 text-sm text-ink shadow-xs transition-colors duration-100 hover:border-accent/40">
                  <Checkbox bind:checked={form.active} disabled={form.processing} />
                  <span>Active</span>
                </label>
              </div>
            </div>

            <div class="border-t border-line pt-4">
              <div class="flex items-center justify-between gap-2">
                <Label>Storage Keys</Label>
                <Button type="button" variant="outline" size="sm" onclick={addKeyRow}>
                  Add key
                </Button>
              </div>

              {#if keyRows.length > 0}
                <div class="mt-3 space-y-2" transition:slide={{ duration: 200 }}>
                  {#each keyRows as row, index (index)}
                    <div class="grid grid-cols-[1fr_1fr_auto] gap-2" transition:slide={{ duration: 150 }}>
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

            <div class="flex items-center justify-end gap-2 pt-2">
              <Button type="button" variant="outline" onclick={closeDialog} disabled={form.processing}>
                Cancel
              </Button>
              <Button type="submit" disabled={form.processing}>
                {form.processing
                  ? "Saving…"
                  : editingId !== null
                    ? "Update storage"
                    : "Create storage"}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
{/if}
