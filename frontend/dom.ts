export function elementsIncludingRoot(root: ParentNode, selector: string): HTMLElement[] {
    const elements: HTMLElement[] = [];

    if (root instanceof HTMLElement && root.matches(selector)) {
        elements.push(root);
    }

    for (const element of root.querySelectorAll<HTMLElement>(selector)) {
        elements.push(element);
    }

    return elements;
}

export function documentFromRoot(root: ParentNode): Document {
    if (root instanceof Document) {
        return root;
    }

    return root.ownerDocument ?? document;
}
