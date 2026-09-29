import type { NextConfig } from "next";

const nextConfig: NextConfig = {
    reactStrictMode: false,
    experimental: {
        cpus: 1,
    },
};

module.exports = {
    allowedDevOrigins: ["0492061z.goralys.test"],
};

export default nextConfig;
