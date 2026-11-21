<script lang="ts">
  import { router, useForm, Link } from "@inertiajs/svelte";
  import AuthShell from "@/components/auth/auth-shell.svelte";
  import GoogleButton from "@/components/auth/google-button.svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { PasswordInput } from "@/components/ui/password-input/index.js";
  import { Toast } from "@/components/ui/toast/index.js";

  interface Props {
    status?: string | null;
    loginStep?: "email" | "password" | "otp" | null;
    loginEmail?: string | null;
    hasPassword?: boolean;
  }

  let {
    status = null,
    loginStep = null,
    loginEmail = null,
    hasPassword = false,
  }: Props = $props();

  let step = $state<"email" | "password" | "otp">(loginStep ?? "email");
  let accountHasPassword = $state(hasPassword);
  let sendingOtp = $state(false);
  let showGoogleToast = $state(false);

  const form = useForm({
    email: loginEmail ?? "",
    password: "",
    otp: "",
    remember: true,
  });

  $effect(() => {
    if (loginStep) {
      step = loginStep;
      accountHasPassword = hasPassword;
      if (loginEmail) {
        form.email = loginEmail;
      }
    }
  });

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();

    if (step === "email") {
      form.post("/login/identify", {
        preserveScroll: true,
      });
      return;
    }

    if (step === "password") {
      form.post("/login", {
        onFinish: () => form.reset("password"),
      });
      return;
    }

    form.post("/login/otp");
  }

  function handleSendOtp() {
    sendingOtp = true;
    form.clearErrors();
    router.post(
      "/login/otp/send",
      { email: form.email },
      {
        preserveScroll: true,
        onFinish: () => {
          sendingOtp = false;
        },
      },
    );
  }

  function resetToEmail() {
    form.clearErrors();
    form.reset("password", "otp");
    step = "email";
  }

  function handleGoogle() {
    showGoogleToast = true;
  }
</script>

<AuthShell
  title="Welcome back"
  description={step === "email"
    ? "Enter your email to continue to your account."
    : step === "password"
      ? "Enter your password or use an email code to sign in."
      : "Enter the 6-digit login code sent to your email."}
>
  <form class="grid gap-5" onsubmit={handleSubmit} novalidate>
    {#if step === "email"}
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
    {:else}
      <div class="flex items-center justify-between gap-2 rounded-md border border-line bg-surface-muted px-3 py-2 text-sm">
        <span class="truncate font-medium text-ink">{form.email}</span>
        <button
          type="button"
          onclick={resetToEmail}
          class="shrink-0 cursor-pointer text-xs font-medium text-accent hover:underline"
        >
          Change
        </button>
      </div>
    {/if}

    {#if step === "password"}
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
    {/if}

    {#if step === "otp"}
      <div class="grid gap-2">
        <div class="flex items-center justify-between gap-2">
          <Label for="otp">Login Code (OTP)</Label>
          <button
            type="button"
            onclick={handleSendOtp}
            disabled={sendingOtp}
            class="cursor-pointer text-sm font-medium text-accent hover:underline disabled:cursor-not-allowed disabled:opacity-50"
          >
            {sendingOtp ? "Sending…" : "Resend code"}
          </button>
        </div>
        <Input
          id="otp"
          type="text"
          inputmode="numeric"
          maxlength={6}
          placeholder="Enter 6-digit code"
          autocomplete="one-time-code"
          bind:value={form.otp}
          aria-invalid={Boolean(form.errors.otp)}
        />
        {#if form.errors.otp}
          <p class="text-xs text-destructive">{form.errors.otp}</p>
        {/if}
      </div>
    {/if}

    {#if step !== "email"}
      <label class="flex items-center gap-2 text-sm text-muted">
        <input
          type="checkbox"
          bind:checked={form.remember}
          class="size-4 rounded border border-line-strong accent-[var(--accent)]"
        />
        Remember me
      </label>
    {/if}

    {#if status}
      <p class="rounded-md bg-accent-soft px-3 py-2 text-sm text-accent">
        {status}
      </p>
    {/if}

    <Button type="submit" class="w-full" disabled={form.processing || sendingOtp}>
      {#if step === "email"}
        {form.processing ? "Checking…" : "Continue"}
      {:else if step === "password"}
        {form.processing ? "Signing in…" : "Sign in"}
      {:else}
        {form.processing ? "Verifying…" : "Verify & sign in"}
      {/if}
    </Button>

    {#if step === "password"}
      <Button
        type="button"
        variant="outline"
        class="w-full"
        disabled={form.processing || sendingOtp}
        onclick={handleSendOtp}
      >
        {sendingOtp ? "Sending OTP…" : "Sign in with email OTP instead"}
      </Button>
    {:else if step === "otp" && accountHasPassword}
      <Button
        type="button"
        variant="outline"
        class="w-full"
        disabled={form.processing || sendingOtp}
        onclick={() => {
          form.clearErrors();
          form.otp = "";
          step = "password";
        }}
      >
        Use password instead
      </Button>
    {/if}

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
  description="Google sign-in is still under development and not available yet. Please sign in using your email address."
  onclose={() => (showGoogleToast = false)}
/>
