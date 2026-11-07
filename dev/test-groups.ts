// Group sync checks against the running sandbox. Run with: dev/idp-login --test-groups
// For each scenario: set plugin settings/hooks (dev/.runtime/saml-sp.overrides.json), give the test user a
// starting set of groups, POST a signed Response (groups attribute: Editors) to the ACS, then compare the
// user's stored groups with what's expected. Not part of the plugin.
import { resolve } from "node:path";
import { rm } from "node:fs/promises";

const [dir, samlToolDir] = process.argv.slice(2);
const runtime = resolve(dir, ".runtime");
const overridesPath = resolve(runtime, "saml-sp.overrides.json");
const configPath = resolve(runtime, "saml-tool.config.ts");

const info = await Bun.file(resolve(runtime, "sandbox.json")).json();
if (!info.groups) {
    console.error("✖ This sandbox predates --test-groups. Run dev/up again.");
    process.exit(1);
}
const { responseConfig } = await import(configPath);
const email: string = responseConfig.subject.nameId;
const editors: number = info.groups.editors;

type Scenario = {
    name: string;
    start: string[];
    settings?: Record<string, unknown>;
    hooks?: Record<string, unknown>;
    expect: string[];
};

// Every Response asserts groups: ["Editors"]
const scenarios: Scenario[] = [
    {
        name: "syncGroups off, mergeExistingGroups off: existing groups are kept (#256)",
        start: ["reviewers"],
        settings: { syncGroups: false, mergeExistingGroups: false },
        expect: ["reviewers"],
    },
    {
        name: "syncGroups off, mergeExistingGroups off: defaults are added to existing groups (#256)",
        start: ["reviewers"],
        settings: { syncGroups: false, mergeExistingGroups: false, defaultGroupAssignments: [editors] },
        expect: ["editors", "reviewers"],
    },
    {
        name: "merging: group assigned during login after the groups were cached is kept (#257)",
        start: [],
        settings: { syncGroups: true, mergeExistingGroups: true },
        hooks: { readGroupsBeforeSync: true, assignDuringLogin: ["reviewers"] },
        expect: ["editors", "reviewers"],
    },
    {
        name: "merging: asserted groups are added to existing groups",
        start: ["reviewers"],
        settings: { syncGroups: true, mergeExistingGroups: true },
        expect: ["editors", "reviewers"],
    },
    {
        name: "not merging: asserted groups replace existing groups",
        start: ["reviewers"],
        settings: { syncGroups: true, mergeExistingGroups: false },
        expect: ["editors"],
    },
];

// As www-data (the web server user, which owns storage/); the last line is the command's output
const craft = async (...args: string[]) =>
    (await Bun.$`docker compose -f ${resolve(dir, "compose.yaml")} exec -T -u www-data web php craft ${args} --interactive=0`.text())
        .trim()
        .split("\n")
        .pop()!;

async function login(): Promise<number> {
    const out = await Bun.$`bun send-response.ts -c ${configPath} -o encoded`.cwd(samlToolDir).text();
    const encoded = out.trim().split("\n").pop()!;
    const res = await fetch(info.sp.acsUrl, {
        method: "POST",
        redirect: "manual",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ SAMLResponse: encoded, RelayState: responseConfig.relayState ?? "" }),
    });
    return res.status;
}

let failed = 0;
try {
    for (const s of scenarios) {
        await Bun.write(overridesPath, JSON.stringify({ settings: s.settings ?? {}, hooks: s.hooks ?? {} }, null, 2));
        await craft("sandbox/set-groups", email, s.start.join(","));

        const status = await login();
        const actual = await craft("sandbox/groups", email);
        const expected = s.expect.length ? [...s.expect].sort().join(",") : "-";
        const ok = status >= 300 && status < 400 && actual === expected;
        if (!ok) failed++;

        console.log(`${ok ? "✔" : "✖"} ${s.name}`);
        if (!ok) console.log(`    login HTTP ${status}; groups: expected ${expected}, got ${actual}`);
    }
} finally {
    await rm(overridesPath, { force: true });
}

console.log(failed ? `\n✖ ${failed} of ${scenarios.length} failed` : `\n✔ All ${scenarios.length} passed`);
process.exit(failed ? 1 : 0);
