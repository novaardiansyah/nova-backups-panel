<script lang="ts">
  import { useForm } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { PasswordInput } from "@/components/ui/password-input/index.js";
  import { getClientTranslations } from "@/lib/i18n.js";
  import type { LocalePreference, ThemePreference } from "@/lib/theme.js";

  export interface ClientUser {
    id: number;
    name: string;
    email: string;
    emailVerifiedAt: string | null;
    createdAt: string | null;
    hasPassword: boolean;
    theme: ThemePreference;
    locale: LocalePreference;
  }

  interface Props {
    user: ClientUser;
    status?: string | null;
  }

  let { user, status = null }: Props = $props();

  const copy = $derived(getClientTranslations(user.locale));

  const form = useForm({
    name: "",
    current_password: "",
    password: "",
    password_confirmation: "",
  });

  $effect(() => {
    form.name = user.name;
  });

  function formatDateTime(value: string | null): string {
    if (!value) return "—";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "—";
    return date.toLocaleDateString(undefined, {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  }

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.put("/client/profile", {
      preserveScroll: true,
      onSuccess: () => {
        form.reset("current_password", "password", "password_confirmation");
      },
      onError: () => {
        form.reset("current_password", "password", "password_confirmation");
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
          <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
      </span>
      <div class="min-w-0">
        <h2 class="text-lg font-semibold tracking-tight text-ink">{copy.profile.title}</h2>
        <p class="mt-1 text-sm leading-6 text-muted">
          {copy.profile.description}
        </p>
      </div>
    </div>

    <dl class="mt-5 grid gap-px overflow-hidden rounded-lg border border-line bg-line sm:grid-cols-2">
      <div class="bg-page px-4 py-3">
        <dt class="text-[10px] uppercase tracking-[0.1em] text-faint">{copy.profile.memberSince}</dt>
        <dd class="mt-1 text-sm text-ink">{formatDateTime(user.createdAt)}</dd>
      </div>
      <div class="bg-page px-4 py-3">
        <dt class="text-[10px] uppercase tracking-[0.1em] text-faint">{copy.profile.status}</dt>
        <dd class="mt-1 flex items-center gap-1.5 text-sm text-ink">
          <span
            class="h-1.5 w-1.5 rounded-full {user.emailVerifiedAt === null ? 'bg-danger' : 'bg-accent'}"
          ></span>
          {user.emailVerifiedAt === null ? copy.profile.unverified : copy.profile.verified}
        </dd>
      </div>
    </dl>
  </div>

  <form
    class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6"
    onsubmit={handleSubmit}
    novalidate
  >
    <div class="space-y-4">
      {#if status}
        <p class="rounded-md bg-accent-soft px-3 py-2 text-sm text-accent">
          {status}
        </p>
      {/if}

      <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
          <Label for="profile-name">{copy.profile.fullName}</Label>
          <Input
            id="profile-name"
            type="text"
            placeholder={copy.profile.fullNamePlaceholder}
            autocomplete="name"
            bind:value={form.name}
            disabled={form.processing}
            aria-invalid={Boolean(form.errors.name)}
          />
          {#if form.errors.name}
            <p class="text-xs text-destructive">{form.errors.name}</p>
          {/if}
        </div>

        <div class="grid gap-2">
          <Label for="profile-email">{copy.profile.email}</Label>
          <Input
            id="profile-email"
            type="email"
            value={user.email}
            readonly
            disabled
            class="cursor-not-allowed bg-surface-muted text-muted"
          />
        </div>
      </div>
      <p class="text-xs leading-5 text-faint">
        {copy.profile.emailHint}
      </p>
    </div>

    <div class="mt-6 border-t border-line pt-5">
      <h3 class="text-sm font-semibold text-ink">
        {user.hasPassword ? copy.profile.changePasswordTitle : copy.profile.setPasswordTitle}
      </h3>
      <p class="mt-1 text-xs leading-5 text-faint">
        {user.hasPassword
          ? copy.profile.changePasswordDescription
          : copy.profile.setPasswordDescription}
      </p>

      <div class="mt-4 grid gap-4 {user.hasPassword ? 'sm:grid-cols-3' : 'sm:grid-cols-2'}">
        {#if user.hasPassword}
          <div class="grid gap-2">
            <Label for="profile-current-password">{copy.profile.currentPassword}</Label>
            <PasswordInput
              id="profile-current-password"
              placeholder={copy.profile.currentPasswordPlaceholder}
              autocomplete="current-password"
              bind:value={form.current_password}
              disabled={form.processing}
              aria-invalid={Boolean(form.errors.current_password)}
            />
            {#if form.errors.current_password}
              <p class="text-xs text-destructive">{form.errors.current_password}</p>
            {/if}
          </div>
        {/if}

        <div class="grid gap-2">
          <Label for="profile-new-password">{copy.profile.newPassword}</Label>
          <PasswordInput
            id="profile-new-password"
            placeholder={copy.profile.newPasswordPlaceholder}
            autocomplete="new-password"
            bind:value={form.password}
            disabled={form.processing}
            aria-invalid={Boolean(form.errors.password)}
          />
          {#if form.errors.password}
            <p class="text-xs text-destructive">{form.errors.password}</p>
          {/if}
        </div>

        <div class="grid gap-2">
          <Label for="profile-confirm-password">{copy.profile.confirmPassword}</Label>
          <PasswordInput
            id="profile-confirm-password"
            placeholder={copy.profile.confirmPasswordPlaceholder}
            autocomplete="new-password"
            bind:value={form.password_confirmation}
            disabled={form.processing}
            aria-invalid={Boolean(form.errors.password_confirmation)}
          />
          {#if form.errors.password_confirmation}
            <p class="text-xs text-destructive">{form.errors.password_confirmation}</p>
          {/if}
        </div>
      </div>
    </div>

    <div class="mt-5 flex">
      <Button type="submit" class="w-full sm:w-auto" disabled={form.processing}>
        {form.processing ? copy.profile.saving : copy.profile.save}
      </Button>
    </div>
  </form>
</div>
