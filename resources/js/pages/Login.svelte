<script lang="ts">
  import { useForm, Link } from "@inertiajs/svelte";
  import AuthShell from "@/components/auth/auth-shell.svelte";
  import GoogleButton from "@/components/auth/google-button.svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { PasswordInput } from "@/components/ui/password-input/index.js";
  import { Toast } from "@/components/ui/toast/index.js";

  interface Props {
    status?: string | null;
  }

  let { status = null }: Props = $props();

  let showGoogleToast = $state(false);

  const form = useForm({
    email: "",
    password: "",
    remember: true,
  });

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.post("/login", {
      onFinish: () => form.reset("password"),
    });
  }

  function handleGoogle() {
    showGoogleToast = true;
  }
</script>

<AuthShell
  title="Welcome back"
  description="Sign in to manage your backup jobs."
>
  <form class="grid gap-5" onsubmit={handleSubmit} novalidate>
    <div class="grid gap-2">
      <Label for="email">Email</Label>
      <Input
        id="email"
        type="email"
        placeholder="you@example.com"
        autocomplete="email"
        bind:value={form.email}
        aria-invalid={Boolean(form.errors.email)}
      />
      {#if form.errors.email}
        <p class="text-xs text-destructive">{form.errors.email}</p>
      {/if}
    </div>

    <div class="grid gap-2">
      <div class="flex items-center justify-between gap-2">
        <Label for="password">Password</Label>
        <Link
          href="/forgot-password"
          class="cursor-pointer text-sm font-medium text-accent hover:underline"
        >
          Forgot password?
        </Link>
      </div>
      <PasswordInput
        id="password"
        placeholder="Enter your password"
        autocomplete="current-password"
        bind:value={form.password}
        aria-invalid={Boolean(form.errors.password)}
      />
      {#if form.errors.password}
        <p class="text-xs text-destructive">{form.errors.password}</p>
      {/if}
    </div>

    <label class="flex items-center gap-2 text-sm text-muted">
      <input
        type="checkbox"
        bind:checked={form.remember}
        class="size-4 rounded border border-line-strong accent-[var(--accent)]"
      />
      Remember me
    </label>

    {#if status}
      <p class="rounded-md bg-accent-soft px-3 py-2 text-sm text-accent">
        {status}
      </p>
    {/if}

    <Button type="submit" class="w-full" disabled={form.processing}>
      {form.processing ? "Signing in…" : "Sign in"}
    </Button>

    <div class="flex justify-center">
      <span class="text-xs font-medium uppercase tracking-wide text-faint">or</span>
    </div>

    <GoogleButton onclick={handleGoogle} />

    <p class="border-t border-line pt-4 text-center text-sm text-muted">
      Don't have an account?
      <Link href="/register" class="cursor-pointer font-medium text-accent hover:underline">
        Create one
      </Link>
    </p>
  </form>
</AuthShell>

<Toast
  open={showGoogleToast}
  title="Feature in development"
  description="Google sign-in is still under development and not available yet. Please sign in using the manual email and password form."
  onclose={() => (showGoogleToast = false)}
/>
