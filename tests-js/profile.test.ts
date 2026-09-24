import { existsSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
import { describe, expect, it } from 'vitest';

interface ProjectProfile {
    readonly schemaVersion: number;
    readonly id: string;
    readonly extends: {
        readonly repository: string;
        readonly contractVersion: number;
    };
    readonly references: readonly string[];
    readonly rule: string;
}

const repositoryRoot = process.cwd();

describe('Latte project profile', () => {
    it('extends the shared contract and references repository files', () => {
        const profile = JSON.parse(
            readFileSync(join(repositoryRoot, 'scaffold/profile.json'), 'utf8'),
        ) as ProjectProfile;

        expect(profile.schemaVersion).toBe(1);
        expect(profile.id).toBe('latte');
        expect(profile.extends).toEqual({
            repository: 'Websonette/web-application',
            contractVersion: 1,
        });

        for (const reference of [...profile.references, profile.rule]) {
            expect(reference).not.toContain('..');
            expect(existsSync(join(repositoryRoot, reference)), reference).toBe(true);
        }
    });
});
