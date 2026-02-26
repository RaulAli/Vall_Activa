type AuthChannelMessage = "logout" | "login";

const channel = new BroadcastChannel("vamo-auth");

export function broadcastAuth(message: AuthChannelMessage): void {
    channel.postMessage(message);
}

export function onAuthBroadcast(handler: (message: AuthChannelMessage) => void): () => void {
    const listener = (e: MessageEvent<AuthChannelMessage>) => handler(e.data);
    channel.addEventListener("message", listener);
    return () => channel.removeEventListener("message", listener);
}
