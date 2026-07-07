import type { NextConfig } from "next";

const nextConfig: NextConfig = {
    reactStrictMode: false,
};

module.exports = {
    allowedDevOrigins: ["relight-unashamed-scrambled.ngrok-free.dev"],
};

export default nextConfig;
